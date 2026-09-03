<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Meta\FieldMetaService;
use App\Core\Meta\ModuleMetaService;
use App\Core\Permissions\PermissionEngine;
use App\Enums\FieldAccessLevel;
use App\Http\Controllers\Controller;
use App\Models\FieldMeta;
use App\Models\ModuleMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleMetaController extends Controller
{
    public function __construct(
        protected ModuleMetaService $moduleMetaService,
        protected FieldMetaService $fieldMetaService,
        protected PermissionEngine $permissionEngine
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $modules = $this->moduleMetaService->getModulesForOrganization($user->organization_id);

        if (! $user->isAdmin()) {
            $modules = $modules->map(function (ModuleMeta $module) use ($user) {
                return $this->filterModuleFieldsForUser($user, $module);
            });
        }

        return response()->json([
            'data' => $modules,
        ]);
    }

    public function show(Request $request, string $apiName): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $apiName);

        if (! $module) {
            return response()->json(['message' => "Module [{$apiName}] not found."], 404);
        }

        if (! $user->isAdmin()) {
            $module = $this->filterModuleFieldsForUser($user, $module);
        }

        return response()->json([
            'data' => $module,
        ]);
    }

    protected function filterModuleFieldsForUser($user, ModuleMeta $module): ModuleMeta
    {
        $accessibleFields = $module->fields->filter(function (FieldMeta $field) use ($user) {
            $access = $this->permissionEngine->getFieldAccessLevel($user, $field);

            return $access !== FieldAccessLevel::HIDDEN;
        })->map(function (FieldMeta $field) use ($user) {
            $access = $this->permissionEngine->getFieldAccessLevel($user, $field);
            if ($access === FieldAccessLevel::READ) {
                $field->is_readonly = true;
            }
            $field->access_level = $access->value;

            return $field;
        })->values();

        $module->setRelation('fields', $accessibleFields);

        return $module;
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'singular_name' => 'nullable|string|max:100',
            'api_name' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'title_field' => 'nullable|string|max:100',
            'layout' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $module = $this->moduleMetaService->createModule($user->organization_id, $validated);

        return response()->json([
            'message' => 'Module created successfully.',
            'data' => $module,
        ], 201);
    }

    public function update(Request $request, string $apiName): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $module = $this->moduleMetaService->findByApiName($user->organization_id, $apiName);
        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'singular_name' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'layout' => 'nullable|array',
            'options' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $module = $this->moduleMetaService->updateModule($module, $validated);

        return response()->json([
            'message' => 'Module updated successfully.',
            'data' => $module,
        ]);
    }

    public function updateLayout(Request $request, string $apiName): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $module = $this->moduleMetaService->findByApiName($user->organization_id, $apiName);
        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $validated = $request->validate([
            'layout' => 'required|array',
        ]);

        $module = $this->moduleMetaService->updateLayout($module, $validated['layout']);

        return response()->json([
            'message' => 'Layout updated successfully.',
            'data' => $module,
        ]);
    }

    public function storeField(Request $request, string $apiName): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $module = $this->moduleMetaService->findByApiName($user->organization_id, $apiName);
        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'api_name' => 'nullable|string|max:100',
            'type' => 'required|string',
            'is_required' => 'boolean',
            'is_unique' => 'boolean',
            'is_readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'options' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $field = $this->fieldMetaService->createField($module, $validated);

        return response()->json([
            'message' => 'Field added successfully.',
            'data' => $field,
        ], 201);
    }

    public function updateField(Request $request, string $apiName, int $fieldId): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $module = $this->moduleMetaService->findByApiName($user->organization_id, $apiName);
        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $field = $module->fields->find($fieldId);
        if (! $field) {
            return response()->json(['message' => 'Field not found.'], 404);
        }

        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:100',
            'is_required' => 'boolean',
            'is_unique' => 'boolean',
            'is_readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'options' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $field = $this->fieldMetaService->updateField($field, $validated);

        return response()->json([
            'message' => 'Field updated successfully.',
            'data' => $field,
        ]);
    }
}
