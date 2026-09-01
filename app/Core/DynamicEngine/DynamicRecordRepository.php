<?php

declare(strict_types=1);

namespace App\Core\DynamicEngine;

use App\Core\Permissions\PermissionEngine;
use App\Models\CrmRecord;
use App\Models\FieldMeta;
use App\Models\ModuleCustomView;
use App\Models\ModuleMeta;
use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DynamicRecordRepository
{
    public function __construct(
        protected PermissionEngine $permissionEngine,
        protected LookupResolver $lookupResolver
    ) {}

    /**
     * Paginate CRM records applying multi-tenancy, permissions, filters, search, and sorting.
     */
    public function paginate(User $user, ModuleMeta $module, array $params = []): LengthAwarePaginator
    {
        $query = CrmRecord::with(['owner:id,name,email']);

        // 1. Apply Permission Scopes (Module CRUD & Role Sharing)
        $this->permissionEngine->applyRecordAccessScope($query, $user, $module, 'view');

        // 2. Apply Custom View / Saved Filters if specified
        if (! empty($params['saved_filter_id'])) {
            $savedFilter = SavedFilter::where('organization_id', $user->organization_id)
                ->where('module_id', $module->id)
                ->find($params['saved_filter_id']);

            if ($savedFilter && ! empty($savedFilter->conditions)) {
                $this->applyFilterConditions($query, $module, $savedFilter->conditions, $savedFilter->logic);
            }
        } elseif (! empty($params['filters']) && is_array($params['filters'])) {
            $this->applyFilterConditions($query, $module, $params['filters'], $params['logic'] ?? 'and');
        }

        // 3. Global Search across text fields
        if (! empty($params['search'])) {
            $this->applyGlobalSearch($query, $module, (string) $params['search']);
        }

        // 4. Sorting
        $sortBy = $params['sort_by'] ?? 'id';
        $sortDir = strtolower($params['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $this->applySorting($query, $module, $sortBy, $sortDir);

        // 5. Pagination
        $perPage = min(max((int) ($params['per_page'] ?? 25), 1), 100);
        $paginator = $query->paginate($perPage);

        // 6. Resolve Lookups for the current page
        $lookupMap = $this->lookupResolver->resolveBatch($module, $paginator->getCollection());

        // 7. Sanitize records and attach resolved lookups
        $paginator->getCollection()->transform(function (CrmRecord $record) use ($user, $module, $lookupMap) {
            $sanitized = $this->permissionEngine->sanitizeRecordForUser($user, $record, $module->fields);
            $sanitized['lookups'] = $lookupMap[$record->id] ?? [];

            return $sanitized;
        });

        return $paginator;
    }

    public function findById(User $user, ModuleMeta $module, int $id): ?array
    {
        $record = CrmRecord::with(['owner:id,name,email', 'timeline.user:id,name', 'notes.user:id,name', 'tags'])
            ->where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->find($id);

        if ($record === null) {
            return null;
        }

        if (! $this->permissionEngine->canAccessRecord($user, $record, 'view')) {
            return null;
        }

        $lookupMap = $this->lookupResolver->resolveBatch($module, new \Illuminate\Database\Eloquent\Collection([$record]));
        $sanitized = $this->permissionEngine->sanitizeRecordForUser($user, $record, $module->fields);
        $sanitized['lookups'] = $lookupMap[$record->id] ?? [];
        $sanitized['tags'] = $record->tags->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color]);
        $sanitized['notes'] = $record->notes;
        $sanitized['timeline'] = $record->timeline;

        return $sanitized;
    }

    protected function applyFilterConditions(Builder $query, ModuleMeta $module, array $conditions, string $logic = 'and'): void
    {
        $boolean = strtolower($logic) === 'or' ? 'or' : 'and';

        $query->where(function (Builder $subQuery) use ($module, $conditions, $boolean) {
            foreach ($conditions as $condition) {
                $fieldApi = $condition['field'] ?? null;
                $operator = strtolower($condition['operator'] ?? '=');
                $value = $condition['value'] ?? null;

                if (! $fieldApi) {
                    continue;
                }

                $field = $module->getField($fieldApi);
                $expression = $this->getJsonFieldExpression($fieldApi, $field);

                $clause = match ($operator) {
                    '=' => fn ($q) => $q->whereRaw("{$expression} = ?", [$value]),
                    '!=', '<>' => fn ($q) => $q->whereRaw("({$expression} != ? OR {$expression} IS NULL)", [$value]),
                    '>' => fn ($q) => $q->whereRaw("{$expression} > ?", [$value]),
                    '>=' => fn ($q) => $q->whereRaw("{$expression} >= ?", [$value]),
                    '<' => fn ($q) => $q->whereRaw("{$expression} < ?", [$value]),
                    '<=' => fn ($q) => $q->whereRaw("{$expression} <= ?", [$value]),
                    'like', 'contains' => fn ($q) => $q->whereRaw("LOWER({$expression}) LIKE LOWER(?)", ["%{$value}%"]),
                    'starts_with' => fn ($q) => $q->whereRaw("LOWER({$expression}) LIKE LOWER(?)", ["{$value}%"]),
                    'ends_with' => fn ($q) => $q->whereRaw("LOWER({$expression}) LIKE LOWER(?)", ["%{$value}"]),
                    'in' => fn ($q) => is_array($value) && ! empty($value)
                        ? $q->whereRaw("{$expression} IN (" . implode(',', array_fill(0, count($value), '?')) . ")", $value)
                        : null,
                    'is_null', 'empty' => fn ($q) => $q->whereRaw("({$expression} IS NULL OR {$expression} = '')"),
                    'is_not_null', 'not_empty' => fn ($q) => $q->whereRaw("({$expression} IS NOT NULL AND {$expression} != '')"),
                    default => fn ($q) => $q->whereRaw("{$expression} = ?", [$value]),
                };

                if ($clause !== null) {
                    if ($boolean === 'or') {
                        $subQuery->orWhere($clause);
                    } else {
                        $subQuery->where($clause);
                    }
                }
            }
        });
    }

    protected function applyGlobalSearch(Builder $query, ModuleMeta $module, string $search): void
    {
        $textFields = $module->fields->filter(fn (FieldMeta $f) => in_array($f->type, ['string', 'text', 'email', 'phone', 'url'], true));

        if ($textFields->isEmpty()) {
            return;
        }

        $query->where(function (Builder $subQuery) use ($textFields, $search) {
            foreach ($textFields as $field) {
                $expression = $this->getJsonFieldExpression($field->api_name, $field);
                $subQuery->orWhereRaw("LOWER({$expression}) LIKE LOWER(?)", ["%{$search}%"]);
            }
        });
    }

    protected function applySorting(Builder $query, ModuleMeta $module, string $sortBy, string $sortDir): void
    {
        // Native columns
        if (in_array($sortBy, ['id', 'created_at', 'updated_at', 'owner_id', 'version'], true)) {
            $query->orderBy($sortBy, $sortDir);

            return;
        }

        // Dynamic JSON field
        $field = $module->getField($sortBy);
        if ($field) {
            $expression = $this->getJsonFieldExpression($sortBy, $field);
            $query->orderByRaw("{$expression} {$sortDir}");
        } else {
            $query->orderBy('id', $sortDir);
        }
    }

    protected function getJsonFieldExpression(string $fieldApi, ?FieldMeta $field): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $expr = "data->>'{$fieldApi}'";

            if ($field && $field->typeEnum()->isNumeric()) {
                return "NULLIF({$expr}, '')::numeric";
            }

            if ($field && $field->typeEnum()->isDateOrTime()) {
                return "NULLIF({$expr}, '')::timestamp";
            }

            return $expr;
        }

        // SQLite / MySQL
        $expr = "json_extract(data, '$.\"{$fieldApi}\"')";

        if ($field && $field->typeEnum()->isNumeric()) {
            return "CAST({$expr} AS NUMERIC)";
        }

        return $expr;
    }
}
