<?php

declare(strict_types=1);

namespace App\Core\Meta;

use App\Models\FieldMeta;
use App\Models\ModuleMeta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FieldMetaService
{
    public function createField(ModuleMeta $module, array $data): FieldMeta
    {
        $apiName = Str::slug($data['api_name'] ?? $data['label'], '_');

        $field = FieldMeta::create([
            'module_id' => $module->id,
            'api_name' => $apiName,
            'label' => $data['label'],
            'type' => $data['type'],
            'is_required' => $data['is_required'] ?? false,
            'is_unique' => $data['is_unique'] ?? false,
            'is_readonly' => $data['is_readonly'] ?? false,
            'default_value' => $data['default_value'] ?? null,
            'options' => $data['options'] ?? null,
            'sort_order' => $data['sort_order'] ?? ($module->fields()->max('sort_order') + 1),
        ]);

        $this->clearModuleCache($module);

        return $field;
    }

    public function updateField(FieldMeta $field, array $data): FieldMeta
    {
        $field->update($data);
        $this->clearModuleCache($field->module);

        return $field;
    }

    public function deleteField(FieldMeta $field): bool
    {
        $module = $field->module;
        $result = (bool) $field->delete();
        $this->clearModuleCache($module);

        return $result;
    }

    public function reorderFields(ModuleMeta $module, array $fieldOrders): void
    {
        foreach ($fieldOrders as $order => $fieldId) {
            FieldMeta::where('id', $fieldId)
                ->where('module_id', $module->id)
                ->update(['sort_order' => $order]);
        }

        $this->clearModuleCache($module);
    }

    protected function clearModuleCache(?ModuleMeta $module): void
    {
        if ($module === null) {
            return;
        }

        Cache::forget("org_{$module->organization_id}_modules");
        Cache::forget("org_{$module->organization_id}_module_{$module->api_name}");
    }
}
