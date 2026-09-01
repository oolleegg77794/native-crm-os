<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Meta\ModuleMetaService;
use App\Http\Controllers\Controller;
use App\Models\ModuleMeta;
use App\Models\Profile;
use App\Models\ProfileFieldPermission;
use App\Models\ProfilePermission;
use App\Models\SharingPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected ModuleMetaService $moduleMetaService
    ) {}

    public function getMatrix(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $orgId = $user->organization_id;
        $profiles = Profile::where('organization_id', $orgId)->get();
        $modules = ModuleMeta::with(['fields'])->where('organization_id', $orgId)->get();
        $sharing = SharingPermission::where('organization_id', $orgId)->get();
        $profilePermissions = ProfilePermission::whereIn('profile_id', $profiles->pluck('id'))->get();
        $fieldPermissions = ProfileFieldPermission::whereIn('profile_id', $profiles->pluck('id'))->get();

        return response()->json([
            'profiles' => $profiles,
            'modules' => $modules,
            'sharing_permissions' => $sharing,
            'profile_permissions' => $profilePermissions,
            'field_permissions' => $fieldPermissions,
        ]);
    }

    public function updateProfileModulePermissions(Request $request, int $profileId): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'module_id' => 'required|integer|exists:module_meta,id',
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
            'can_export' => 'boolean',
            'can_import' => 'boolean',
        ]);

        $permission = ProfilePermission::updateOrCreate([
            'profile_id' => $profileId,
            'module_id' => $validated['module_id'],
        ], $validated);

        return response()->json([
            'message' => 'Profile permissions updated.',
            'data' => $permission,
        ]);
    }

    public function updateSharingPermission(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'module_id' => 'required|integer|exists:module_meta,id',
            'access_type' => 'required|in:public_read_write,public_read_only,private',
        ]);

        $sharing = SharingPermission::updateOrCreate([
            'organization_id' => $user->organization_id,
            'module_id' => $validated['module_id'],
        ], [
            'access_type' => $validated['access_type'],
        ]);

        return response()->json([
            'message' => 'Sharing rule updated.',
            'data' => $sharing,
        ]);
    }

    public function updateFieldPermission(Request $request, int $profileId): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'field_id' => 'required|integer|exists:field_meta,id',
            'access_level' => 'required|in:write,read,hidden',
        ]);

        $fieldPerm = ProfileFieldPermission::updateOrCreate([
            'profile_id' => $profileId,
            'field_id' => $validated['field_id'],
        ], [
            'access_level' => $validated['access_level'],
        ]);

        return response()->json([
            'message' => 'Field permission updated.',
            'data' => $fieldPerm,
        ]);
    }
}
