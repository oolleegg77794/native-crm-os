<?php

declare(strict_types=1);

namespace App\Core\Permissions;

use App\Enums\FieldAccessLevel;
use App\Enums\SharingAccessType;
use App\Models\CrmRecord;
use App\Models\FieldMeta;
use App\Models\ModuleMeta;
use App\Models\ProfileFieldPermission;
use App\Models\ProfilePermission;
use App\Models\SharingPermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PermissionEngine
{
    public function __construct(
        protected RoleHierarchyService $roleHierarchyService
    ) {}

    // ==========================================
    // LEVEL 1: MODULE-LEVEL PERMISSIONS (CRUD)
    // ==========================================

    public function can(User $user, ModuleMeta $module, string $action): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->profile_id === null) {
            return false;
        }

        $perm = ProfilePermission::where('profile_id', $user->profile_id)
            ->where('module_id', $module->id)
            ->first();

        if ($perm === null) {
            // Default: can view and create, cannot edit/delete without explicit grant
            return in_array($action, ['view', 'create'], true);
        }

        return match ($action) {
            'view' => $perm->can_view,
            'create' => $perm->can_create,
            'edit', 'update' => $perm->can_edit,
            'delete' => $perm->can_delete,
            'export' => $perm->can_export,
            'import' => $perm->can_import,
            default => false,
        };
    }

    // ==========================================
    // LEVEL 2: RECORD-LEVEL (DATA SHARING RULES)
    // ==========================================

    public function canAccessRecord(User $user, CrmRecord $record, string $action = 'view'): bool
    {
        if ($user->organization_id !== $record->organization_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // 1. Must satisfy module-level permissions
        $module = $record->module ?? ModuleMeta::find($record->module_id);
        if ($module === null || ! $this->can($user, $module, $action)) {
            return false;
        }

        // 2. Direct owner always has access
        if ($record->owner_id === $user->id) {
            return true;
        }

        // 3. Sharing Rule check
        $sharing = SharingPermission::where('organization_id', $record->organization_id)
            ->where('module_id', $record->module_id)
            ->first();

        $accessType = $sharing ? $sharing->accessTypeEnum() : SharingAccessType::PUBLIC_READ_WRITE;

        if ($accessType === SharingAccessType::PUBLIC_READ_WRITE) {
            return true;
        }

        if ($accessType === SharingAccessType::PUBLIC_READ_ONLY) {
            return in_array($action, ['view', 'read'], true);
        }

        // 4. If Private, check Role Hierarchy (Manager sees Subordinates' records)
        $subordinateIds = $this->roleHierarchyService->getSubordinateUserIds($user, true);

        return in_array($record->owner_id, $subordinateIds, true);
    }

    /**
     * Scope an Eloquent query to only include records the user has permission to see.
     */
    public function applyRecordAccessScope(Builder $query, User $user, ModuleMeta $module, string $action = 'view'): Builder
    {
        if ($user->isAdmin()) {
            return $query->where('organization_id', $user->organization_id)
                ->where('module_id', $module->id);
        }

        $query->where('organization_id', $user->organization_id)
            ->where('module_id', $module->id);

        $sharing = SharingPermission::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->first();

        $accessType = $sharing ? $sharing->accessTypeEnum() : SharingAccessType::PUBLIC_READ_WRITE;

        if ($accessType === SharingAccessType::PUBLIC_READ_WRITE) {
            return $query;
        }

        if ($accessType === SharingAccessType::PUBLIC_READ_ONLY && in_array($action, ['view', 'read'], true)) {
            return $query;
        }

        // If Private (or writing in Read-Only mode), restrict to self + subordinates
        $accessibleUserIds = $this->roleHierarchyService->getSubordinateUserIds($user, true);

        return $query->whereIn('owner_id', $accessibleUserIds);
    }

    // ==========================================
    // LEVEL 3: FIELD-LEVEL SECURITY (FLS)
    // ==========================================

    public function getFieldAccessLevel(User $user, FieldMeta $field): FieldAccessLevel
    {
        if ($user->isAdmin()) {
            return FieldAccessLevel::WRITE;
        }

        if ($user->profile_id === null) {
            return FieldAccessLevel::WRITE;
        }

        $perm = ProfileFieldPermission::where('profile_id', $user->profile_id)
            ->where('field_id', $field->id)
            ->first();

        if ($perm === null) {
            return FieldAccessLevel::WRITE;
        }

        return $perm->accessLevelEnum();
    }

    /**
     * Sanitize record payload for presentation (removes hidden fields and attaches field permissions).
     *
     * @param Collection<int, FieldMeta>|null $fields
     */
    public function sanitizeRecordForUser(User $user, CrmRecord $record, ?Collection $fields = null): array
    {
        $fields = $fields ?? $record->module->fields;
        $rawData = $record->data ?? [];
        $sanitizedFields = [];

        foreach ($fields as $field) {
            $access = $this->getFieldAccessLevel($user, $field);

            if ($access === FieldAccessLevel::HIDDEN) {
                continue;
            }

            $sanitizedFields[$field->api_name] = [
                'value' => $rawData[$field->api_name] ?? $field->default_value,
                'label' => $field->label,
                'type' => $field->type,
                'access' => $access->value,
                'is_readonly' => $field->is_readonly || ($access === FieldAccessLevel::READ),
            ];
        }

        return [
            'id' => $record->id,
            'module_id' => $record->module_id,
            'owner_id' => $record->owner_id,
            'owner' => $record->owner ? [
                'id' => $record->owner->id,
                'name' => $record->owner->name,
                'email' => $record->owner->email,
            ] : null,
            'is_locked' => $record->is_locked,
            'version' => $record->version,
            'created_at' => $record->created_at?->toISOString(),
            'updated_at' => $record->updated_at?->toISOString(),
            'fields' => $sanitizedFields,
            'raw_values' => collect($sanitizedFields)->mapWithKeys(fn ($item, $k) => [$k => $item['value']])->all(),
        ];
    }

    /**
     * Filter user request input to only allow fields the user has WRITE access to.
     */
    public function filterWritablePayload(User $user, ModuleMeta $module, array $payload): array
    {
        if ($user->isAdmin()) {
            return $payload;
        }

        $fields = $module->fields;
        $filtered = [];

        foreach ($payload as $key => $value) {
            $field = $fields->firstWhere('api_name', $key);

            if ($field === null) {
                continue;
            }

            if ($field->is_readonly) {
                continue;
            }

            $access = $this->getFieldAccessLevel($user, $field);
            if ($access->canWrite()) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
