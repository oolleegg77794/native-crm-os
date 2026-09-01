<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $query = User::with(['role', 'profile', 'department'])
            ->where('organization_id', $orgId);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->query('role_id'));
        }

        if ($request->filled('profile_id')) {
            $query->where('profile_id', $request->query('profile_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->orderBy('name')->paginate($request->integer('per_page', 20));

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('organization_id', $orgId)),
            ],
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'profile_id' => 'required|exists:profiles,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'organization_id' => $orgId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'profile_id' => $validated['profile_id'],
            'department_id' => $validated['department_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($user->load(['role', 'profile', 'department']), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $user = User::with(['role', 'profile', 'department'])
            ->where('organization_id', $orgId)
            ->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $user = User::where('organization_id', $orgId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->where(fn ($q) => $q->where('organization_id', $orgId))
                    ->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
            'role_id' => 'sometimes|required|exists:roles,id',
            'profile_id' => 'sometimes|required|exists:profiles,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load(['role', 'profile', 'department']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $user = User::where('organization_id', $orgId)->findOrFail($id);

        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function getMetadata(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $roles = Role::where('organization_id', $orgId)->get();
        $profiles = Profile::where('organization_id', $orgId)->get();
        $departments = Department::all();

        return response()->json([
            'roles' => $roles,
            'profiles' => $profiles,
            'departments' => $departments,
        ]);
    }
}
