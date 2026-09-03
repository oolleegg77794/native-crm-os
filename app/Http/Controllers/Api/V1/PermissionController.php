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

    public function storeProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_admin' => 'boolean',
            'copy_from_profile_id' => 'nullable|integer|exists:profiles,id',
        ]);

        $profile = Profile::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
        ]);

        // If copying permissions from an existing profile
        if (! empty($validated['copy_from_profile_id'])) {
            $sourceModulePerms = ProfilePermission::where('profile_id', $validated['copy_from_profile_id'])->get();
            foreach ($sourceModulePerms as $perm) {
                ProfilePermission::create([
                    'profile_id' => $profile->id,
                    'module_id' => $perm->module_id,
                    'can_view' => $perm->can_view,
                    'can_create' => $perm->can_create,
                    'can_edit' => $perm->can_edit,
                    'can_delete' => $perm->can_delete,
                    'can_export' => $perm->can_export,
                    'can_import' => $perm->can_import,
                ]);
            }

            $sourceFieldPerms = ProfileFieldPermission::where('profile_id', $validated['copy_from_profile_id'])->get();
            foreach ($sourceFieldPerms as $fPerm) {
                ProfileFieldPermission::create([
                    'profile_id' => $profile->id,
                    'field_id' => $fPerm->field_id,
                    'access_level' => $fPerm->access_level,
                ]);
            }
        }

        return response()->json([
            'message' => 'Profile created successfully.',
            'data' => $profile,
        ], 201);
    }

    public function updateProfile(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $profile = Profile::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $profile->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $profile,
        ]);
    }

    public function destroyProfile(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $profile = Profile::where('organization_id', $user->organization_id)->findOrFail($id);

        if ($profile->is_admin) {
            return response()->json(['message' => 'Cannot delete Administrator profile.'], 422);
        }

        $assignedUsersCount = $profile->users()->count();
        if ($assignedUsersCount > 0) {
            return response()->json([
                'message' => "Cannot delete profile: {$assignedUsersCount} active member(s) are assigned to it. Reassign them first.",
            ], 422);
        }

        $profile->delete();

        return response()->json([
            'message' => 'Profile deleted successfully.',
        ]);
    }
}
