<?php

declare(strict_types=1);

namespace App\Core\Meta;

use App\Enums\FieldType;
use App\Enums\SharingAccessType;
use App\Models\ModuleMeta;
use App\Models\SharingPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ModuleMetaService
{
    public function __construct(
        protected FieldMetaService $fieldMetaService
    ) {}

    /**
     * @return Collection<int, ModuleMeta>
     */
    public function getModulesForOrganization(int $organizationId): Collection
    {
        return ModuleMeta::with(['fields' => fn ($q) => $q->orderBy('sort_order')])
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get();
    }

    public function findByApiName(int $organizationId, string $apiName): ?ModuleMeta
    {
        return ModuleMeta::with(['fields' => fn ($q) => $q->orderBy('sort_order')])
            ->where('organization_id', $organizationId)
            ->where('api_name', $apiName)
            ->first();
    }

    public function createModule(int $organizationId, array $data): ModuleMeta
    {
        $apiName = Str::slug($data['api_name'] ?? $data['name'], '_');

        $module = ModuleMeta::create([
            'organization_id' => $organizationId,
            'name' => $data['name'],
            'singular_name' => $data['singular_name'] ?? Str::singular($data['name']),
            'api_name' => $apiName,
            'icon' => $data['icon'] ?? 'folder',
            'color' => $data['color'] ?? '#3B82F6',
            'title_field' => $data['title_field'] ?? 'name',
            'is_system' => $data['is_system'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'layout' => $data['layout'] ?? null,
            'options' => $data['options'] ?? null,
        ]);

        // Default primary field (e.g. "name" or "title")
        $this->fieldMetaService->createField($module, [
            'api_name' => $data['title_field'] ?? 'name',
            'label' => $data['title_label'] ?? 'Name',
            'type' => FieldType::STRING->value,
            'is_required' => true,
            'is_unique' => false,
            'sort_order' => 1,
        ]);

        // Default Sharing Rule (Public Read/Write)
        SharingPermission::firstOrCreate([
            'organization_id' => $organizationId,
            'module_id' => $module->id,
        ], [
            'access_type' => SharingAccessType::PUBLIC_READ_WRITE->value,
        ]);

        return $module->fresh(['fields']);
    }

    public function updateModule(ModuleMeta $module, array $data): ModuleMeta
    {
        $module->update($data);

        return $module->fresh(['fields']);
    }

    public function updateLayout(ModuleMeta $module, array $layout): ModuleMeta
    {
        $module->update(['layout' => $layout]);

        return $module;
    }

    public function deleteModule(ModuleMeta $module): bool
    {
        return (bool) $module->delete();
    }
}
