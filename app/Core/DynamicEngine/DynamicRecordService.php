<?php

declare(strict_types=1);

namespace App\Core\DynamicEngine;

use App\Core\Permissions\PermissionEngine;
use App\Core\Timeline\ActivityLogger;
use App\Enums\FieldType;
use App\Models\CrmRecord;
use App\Models\ModuleMeta;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DynamicRecordService
{
    public function __construct(
        protected PermissionEngine $permissionEngine,
        protected DynamicValidator $validator,
        protected ActivityLogger $activityLogger
    ) {}

    public function create(User $user, ModuleMeta $module, array $payload, ?int $ownerId = null): CrmRecord
    {
        if (! $this->permissionEngine->can($user, $module, 'create')) {
            throw new AuthorizationException("You do not have permission to create records in module [{$module->name}].");
        }

        // Filter writable fields based on Field-Level Security
        $writablePayload = $this->permissionEngine->filterWritablePayload($user, $module, $payload);

        // Auto-generate autonumber fields if configured
        $writablePayload = $this->generateAutoNumbers($module, $writablePayload);

        // Validate payload
        $validatedData = $this->validator->validate($module, $writablePayload, false);

        return DB::transaction(function () use ($user, $module, $validatedData, $ownerId) {
            $record = CrmRecord::create([
                'organization_id' => $user->organization_id,
                'module_id' => $module->id,
                'owner_id' => $ownerId ?? $user->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'data' => $validatedData,
                'version' => 1,
            ]);

            $this->activityLogger->logCreate($record, $user);

            return $record;
        });
    }

    public function update(User $user, ModuleMeta $module, CrmRecord $record, array $payload): CrmRecord
    {
        if (! $this->permissionEngine->canAccessRecord($user, $record, 'edit')) {
            throw new AuthorizationException("You do not have permission to edit this record.");
        }

        if ($record->is_locked && ! $user->isAdmin()) {
            throw new RuntimeException("This record is locked: " . ($record->locked_reason ?? 'Locked for edits'));
        }

        $writablePayload = $this->permissionEngine->filterWritablePayload($user, $module, $payload);
        $mergedData = array_merge($record->data ?? [], $writablePayload);

        $validatedData = $this->validator->validate($module, $mergedData, true, $record->id);
        $oldData = $record->data ?? [];

        return DB::transaction(function () use ($user, $record, $validatedData, $oldData) {
            $record->update([
                'data' => $validatedData,
                'updated_by' => $user->id,
                'version' => $record->version + 1,
            ]);

            $this->activityLogger->logUpdate($record, $oldData, $user);

            return $record;
        });
    }

    public function delete(User $user, ModuleMeta $module, CrmRecord $record): bool
    {
        if (! $this->permissionEngine->canAccessRecord($user, $record, 'delete')) {
            throw new AuthorizationException("You do not have permission to delete this record.");
        }

        return DB::transaction(function () use ($user, $record) {
            $this->activityLogger->logDelete($record, $user);

            return (bool) $record->delete();
        });
    }

    public function changeOwner(User $user, ModuleMeta $module, CrmRecord $record, int $newOwnerId): CrmRecord
    {
        if (! $this->permissionEngine->canAccessRecord($user, $record, 'edit')) {
            throw new AuthorizationException("You do not have permission to modify this record.");
        }

        $oldOwnerId = $record->owner_id;

        return DB::transaction(function () use ($user, $record, $oldOwnerId, $newOwnerId) {
            $record->update([
                'owner_id' => $newOwnerId,
                'updated_by' => $user->id,
            ]);

            $this->activityLogger->logOwnerChange($record, $oldOwnerId, $newOwnerId, $user);

            return $record;
        });
    }

    public function massUpdate(User $user, ModuleMeta $module, array $recordIds, array $payload): int
    {
        $records = CrmRecord::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->whereIn('id', $recordIds)
            ->get();

        $updatedCount = 0;

        foreach ($records as $record) {
            if ($this->permissionEngine->canAccessRecord($user, $record, 'edit')) {
                $this->update($user, $module, $record, $payload);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    public function massDelete(User $user, ModuleMeta $module, array $recordIds): int
    {
        $records = CrmRecord::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->whereIn('id', $recordIds)
            ->get();

        $deletedCount = 0;

        foreach ($records as $record) {
            if ($this->permissionEngine->canAccessRecord($user, $record, 'delete')) {
                $this->delete($user, $module, $record);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    public function massTag(User $user, ModuleMeta $module, array $recordIds, array $tagIds): int
    {
        $records = CrmRecord::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->whereIn('id', $recordIds)
            ->get();

        $count = 0;

        foreach ($records as $record) {
            if ($this->permissionEngine->canAccessRecord($user, $record, 'edit')) {
                $record->tags()->syncWithoutDetaching($tagIds);
                $count++;
            }
        }

        return $count;
    }

    protected function generateAutoNumbers(ModuleMeta $module, array $payload): array
    {
        $autonumberFields = $module->fields->filter(fn ($f) => $f->typeEnum() === FieldType::AUTONUMBER);

        foreach ($autonumberFields as $field) {
            if (empty($payload[$field->api_name])) {
                $prefix = $field->options['prefix'] ?? strtoupper(substr($module->api_name, 0, 3)) . '-';
                $nextNumber = CrmRecord::where('organization_id', $module->organization_id)
                    ->where('module_id', $module->id)
                    ->count() + 1;

                $payload[$field->api_name] = $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
            }
        }

        return $payload;
    }
}
