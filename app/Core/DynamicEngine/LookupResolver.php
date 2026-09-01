<?php

declare(strict_types=1);

namespace App\Core\DynamicEngine;

use App\Enums\FieldType;
use App\Models\CrmRecord;
use App\Models\ModuleMeta;
use Illuminate\Database\Eloquent\Collection;

class LookupResolver
{
    /**
     * Resolve lookup fields for a collection of records in batch.
     *
     * @param Collection<int, CrmRecord> $records
     * @return array<int, array<string, array{id: int, label: string}|null>>
     */
    public function resolveBatch(ModuleMeta $module, Collection $records): array
    {
        $lookupFields = $module->fields->filter(fn ($f) => $f->typeEnum() === FieldType::LOOKUP);

        if ($lookupFields->isEmpty() || $records->isEmpty()) {
            return [];
        }

        // Map of [target_module_api_name => [record_ids]]
        $targetModuleToIds = [];
        $fieldToTargetModule = [];

        foreach ($lookupFields as $field) {
            $options = $field->options ?? [];
            $targetModuleApi = $options['target_module'] ?? null;

            if (! $targetModuleApi) {
                continue;
            }

            $fieldToTargetModule[$field->api_name] = $targetModuleApi;

            if (! isset($targetModuleToIds[$targetModuleApi])) {
                $targetModuleToIds[$targetModuleApi] = [];
            }

            foreach ($records as $record) {
                $data = $record->data ?? [];
                $lookupId = $data[$field->api_name] ?? null;

                if ($lookupId !== null && is_numeric($lookupId)) {
                    $targetModuleToIds[$targetModuleApi][] = (int) $lookupId;
                }
            }
        }

        // Batch fetch target records per module
        $lookupCache = []; // [target_module => [id => label]]

        foreach ($targetModuleToIds as $targetModuleApi => $ids) {
            $uniqueIds = array_unique($ids);
            if (empty($uniqueIds)) {
                continue;
            }

            $targetModule = ModuleMeta::where('organization_id', $module->organization_id)
                ->where('api_name', $targetModuleApi)
                ->first();

            if ($targetModule === null) {
                continue;
            }

            $titleField = $targetModule->title_field ?: 'name';

            $targetRecords = CrmRecord::where('organization_id', $module->organization_id)
                ->where('module_id', $targetModule->id)
                ->whereIn('id', $uniqueIds)
                ->get();

            foreach ($targetRecords as $targetRecord) {
                $tData = $targetRecord->data ?? [];
                $label = $tData[$titleField] ?? ("#" . $targetRecord->id);
                $lookupCache[$targetModuleApi][$targetRecord->id] = (string) $label;
            }
        }

        // Hydrate lookup results for each record
        $resolvedMap = [];

        foreach ($records as $record) {
            $data = $record->data ?? [];
            $recordResolved = [];

            foreach ($lookupFields as $field) {
                $targetModuleApi = $fieldToTargetModule[$field->api_name] ?? null;
                $lookupId = $data[$field->api_name] ?? null;

                if ($targetModuleApi && $lookupId && isset($lookupCache[$targetModuleApi][(int) $lookupId])) {
                    $recordResolved[$field->api_name] = [
                        'id' => (int) $lookupId,
                        'label' => $lookupCache[$targetModuleApi][(int) $lookupId],
                    ];
                } else {
                    $recordResolved[$field->api_name] = null;
                }
            }

            $resolvedMap[$record->id] = $recordResolved;
        }

        return $resolvedMap;
    }
}
