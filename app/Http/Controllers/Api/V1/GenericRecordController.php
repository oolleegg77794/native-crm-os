<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\DynamicEngine\DynamicRecordRepository;
use App\Core\DynamicEngine\DynamicRecordService;
use App\Core\Meta\ModuleMetaService;
use App\Http\Controllers\Controller;
use App\Models\CrmRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenericRecordController extends Controller
{
    public function __construct(
        protected ModuleMetaService $moduleMetaService,
        protected DynamicRecordRepository $repository,
        protected DynamicRecordService $service
    ) {}

    public function index(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $paginator = $this->repository->paginate($user, $module, $request->all());

        return response()->json($paginator);
    }

    public function show(Request $request, string $moduleApi, int $id): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $record = $this->repository->findById($user, $module, $id);

        if (! $record) {
            return response()->json(['message' => 'Record not found or access denied.'], 404);
        }

        return response()->json([
            'data' => $record,
        ]);
    }

    public function store(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $payload = $request->all();
        $ownerId = $request->input('owner_id') ? (int) $request->input('owner_id') : null;

        $record = $this->service->create($user, $module, $payload, $ownerId);
        $sanitized = $this->repository->findById($user, $module, $record->id);

        return response()->json([
            'message' => 'Record created successfully.',
            'data' => $sanitized,
        ], 201);
    }

    public function update(Request $request, string $moduleApi, int $id): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $record = CrmRecord::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->find($id);

        if (! $record) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $this->service->update($user, $module, $record, $request->all());
        $sanitized = $this->repository->findById($user, $module, $record->id);

        return response()->json([
            'message' => 'Record updated successfully.',
            'data' => $sanitized,
        ]);
    }

    public function destroy(Request $request, string $moduleApi, int $id): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $record = CrmRecord::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->find($id);

        if (! $record) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $this->service->delete($user, $module, $record);

        return response()->json([
            'message' => 'Record deleted successfully.',
        ]);
    }

    public function massUpdate(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'data' => 'required|array',
        ]);

        $updatedCount = $this->service->massUpdate($user, $module, $validated['ids'], $validated['data']);

        return response()->json([
            'message' => "Successfully updated {$updatedCount} records.",
            'updated_count' => $updatedCount,
        ]);
    }

    public function massDelete(Request $request, string $moduleApi): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
        ]);

        $deletedCount = $this->service->massDelete($user, $module, $validated['ids']);

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} records.",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function changeOwner(Request $request, string $moduleApi, int $id): JsonResponse
    {
        $user = $request->user();
        $module = $this->moduleMetaService->findByApiName($user->organization_id, $moduleApi);

        if (! $module) {
            return response()->json(['message' => "Module [{$moduleApi}] not found."], 404);
        }

        $record = CrmRecord::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->find($id);

        if (! $record) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        $validated = $request->validate([
            'new_owner_id' => 'required|integer|exists:users,id',
        ]);

        $this->service->changeOwner($user, $module, $record, (int) $validated['new_owner_id']);

        return response()->json([
            'message' => 'Record owner changed successfully.',
        ]);
    }
}
