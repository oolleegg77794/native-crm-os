<?php

declare(strict_types=1);

namespace App\Core\DynamicEngine;

use App\Enums\FieldType;
use App\Models\CrmRecord;
use App\Models\FieldMeta;
use App\Models\ModuleMeta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DynamicValidator
{
    /**
     * Validate dynamic data payload according to Module field definitions.
     *
     * @throws ValidationException
     */
    public function validate(ModuleMeta $module, array $data, bool $isUpdate = false, ?int $currentRecordId = null): array
    {
        $rules = [];
        $messages = [];

        foreach ($module->fields as $field) {
            $fieldRules = [];
            $apiName = $field->api_name;

            // Required rule
            if ($field->is_required) {
                if ($isUpdate) {
                    $fieldRules[] = 'sometimes';
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'required';
                }
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type rules
            $fieldType = $field->typeEnum();
            switch ($fieldType) {
                case FieldType::STRING:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
                    break;
                case FieldType::TEXT:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:5000';
                    break;
                case FieldType::LONGTEXT:
                    $fieldRules[] = 'string';
                    break;
                case FieldType::INTEGER:
                    $fieldRules[] = 'integer';
                    break;
                case FieldType::DECIMAL:
                case FieldType::CURRENCY:
                case FieldType::PERCENT:
                    $fieldRules[] = 'numeric';
                    break;
                case FieldType::BOOLEAN:
                    $fieldRules[] = 'boolean';
                    break;
                case FieldType::DATE:
                    $fieldRules[] = 'date_format:Y-m-d';
                    break;
                case FieldType::DATETIME:
                    $fieldRules[] = 'date';
                    break;
                case FieldType::TIME:
                    $fieldRules[] = 'date_format:H:i,H:i:s';
                    break;
                case FieldType::EMAIL:
                    $fieldRules[] = 'email';
                    break;
                case FieldType::PHONE:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:50';
                    break;
                case FieldType::URL:
                    $fieldRules[] = 'url';
                    break;
                case FieldType::PICKLIST:
                    $values = array_column($field->options['values'] ?? [], 'key');
                    if (! empty($values)) {
                        $fieldRules[] = 'in:' . implode(',', $values);
                    }
                    break;
                case FieldType::MULTISELECT:
                case FieldType::MULTI_LOOKUP:
                case FieldType::JSON:
                    $fieldRules[] = 'array';
                    break;
                case FieldType::LOOKUP:
                    $fieldRules[] = 'integer';
                    break;
                default:
                    break;
            }

            $rules[$apiName] = $fieldRules;
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check unique fields
        foreach ($module->fields->where('is_unique', true) as $field) {
            $value = $data[$field->api_name] ?? null;
            if ($value === null) {
                continue;
            }

            $query = CrmRecord::where('organization_id', $module->organization_id)
                ->where('module_id', $module->id)
                ->whereJsonContains("data->{$field->api_name}", $value);

            if ($currentRecordId !== null) {
                $query->where('id', '!=', $currentRecordId);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    $field->api_name => ["The {$field->label} has already been taken."],
                ]);
            }
        }

        return $validator->validated();
    }
}
