<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Meta\ModuleMetaService;
use App\Http\Controllers\Controller;
use App\Models\ModuleCustomView;
use App\Models\SavedFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomViewController extends Controller
{
    public function __construct(
        protected ModuleMetaService $moduleMetaService
    ) {}

    public function indexViews(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $views = ModuleCustomView::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->get();

        return response()->json(['data' => $views]);
    }

    public function storeView(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'columns' => 'required|array',
            'sort_by' => 'nullable|string',
            'sort_direction' => 'nullable|in:asc,desc',
            'is_default' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $view = ModuleCustomView::create([
            'organization_id' => $user->organization_id,
            'module_id' => $module->id,
            'user_id' => (! empty($validated['is_public']) && $user->isAdmin()) ? null : $user->id,
            'name' => $validated['name'],
            'columns' => $validated['columns'],
            'sort_by' => $validated['sort_by'] ?? null,
            'sort_direction' => $validated['sort_direction'] ?? 'desc',
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json(['message' => 'Custom view created.', 'data' => $view], 201);
    }

    public function indexFilters(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $filters = SavedFilter::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->get();

        return response()->json(['data' => $filters]);
    }

    public function storeFilter(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => 'Module not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'conditions' => 'required|array',
            'logic' => 'nullable|in:and,or',
            'is_default' => 'boolean',
        ]);

        $filter = SavedFilter::create([
            'organization_id' => $user->organization_id,
            'module_id' => $module->id,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'conditions' => $validated['conditions'],
            'logic' => $validated['logic'] ?? 'and',
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json(['message' => 'Saved filter created.', 'data' => $filter], 201);
    }
}
