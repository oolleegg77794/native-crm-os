<?php

declare(strict_types=1);

use App\Core\Meta\FieldMetaService;
use App\Core\Meta\ModuleMetaService;
use App\Enums\FieldType;
use App\Http\Controllers\Api\V1\CustomViewController;
use App\Http\Controllers\Api\V1\GenericRecordController;
use App\Http\Controllers\Api\V1\ModuleMetaController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\SetTenantContext;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

Route::get('/user', function (Request $request) {
    return $request->user()->load(['organization', 'profile', 'role', 'department']);
})->middleware('auth:sanctum');

// Authentication & Registration Routes
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['organization', 'profile', 'role', 'department'])
            ->where('email', $request->email)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('crm_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    });

    Route::post('/auth/register', function (Request $request) {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_slug' => 'nullable|string|max:255|unique:organizations,slug',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        return DB::transaction(function () use ($request) {
            $slug = Str::slug($request->company_slug ?: $request->company_name);
            if (Organization::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::lower(Str::random(4));
            }

            // 1. Create Organization
            $org = Organization::create([
                'name' => $request->company_name,
                'slug' => $slug,
                'is_active' => true,
            ]);

            // 2. Create Default Profiles
            $adminProfile = Profile::create([
                'organization_id' => $org->id,
                'name' => 'Administrator',
                'description' => 'Full administrative access',
                'is_admin' => true,
            ]);

            Profile::create([
                'organization_id' => $org->id,
                'name' => 'Standard User',
                'description' => 'Standard operational access',
                'is_admin' => false,
            ]);

            // 3. Create Role Tree
            $ceoRole = Role::create([
                'organization_id' => $org->id,
                'name' => 'Chief Executive Officer (CEO)',
            ]);

            $salesDirector = Role::create([
                'organization_id' => $org->id,
                'name' => 'Sales Director',
                'parent_id' => $ceoRole->id,
            ]);

            Role::create([
                'organization_id' => $org->id,
                'name' => 'Sales Representative',
                'parent_id' => $salesDirector->id,
            ]);

            // 4. Create Owner User
            $user = User::create([
                'organization_id' => $org->id,
                'profile_id' => $adminProfile->id,
                'role_id' => $ceoRole->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            // 5. Initialize Standard Modules (Leads & Deals)
            $metaService = app(ModuleMetaService::class);
            $fieldService = app(FieldMetaService::class);

            $leadsMod = $metaService->createModule($org->id, [
                'name' => 'Leads',
                'singular_name' => 'Lead',
                'api_name' => 'leads',
                'icon' => 'user-plus',
                'color' => '#3B82F6',
                'title_field' => 'full_name',
                'title_label' => 'Full Name',
                'layout' => [
                    'sections' => [
                        [
                            'title' => 'Lead Details',
                            'columns' => 2,
                            'fields' => ['full_name', 'company', 'email', 'phone', 'status'],
                        ],
                    ],
                ],
            ]);

            $fieldService->createField($leadsMod, ['label' => 'Company', 'api_name' => 'company', 'type' => FieldType::STRING->value]);
            $fieldService->createField($leadsMod, ['label' => 'Email', 'api_name' => 'email', 'type' => FieldType::EMAIL->value]);
            $fieldService->createField($leadsMod, ['label' => 'Phone', 'api_name' => 'phone', 'type' => FieldType::PHONE->value]);
            $fieldService->createField($leadsMod, [
                'label' => 'Status',
                'api_name' => 'status',
                'type' => FieldType::PICKLIST->value,
                'options' => [
                    'values' => [
                        ['key' => 'new', 'label' => 'New'],
                        ['key' => 'contacted', 'label' => 'Contacted'],
                        ['key' => 'qualified', 'label' => 'Qualified'],
                    ],
                ],
            ]);

            $dealsMod = $metaService->createModule($org->id, [
                'name' => 'Deals',
                'singular_name' => 'Deal',
                'api_name' => 'deals',
                'icon' => 'briefcase',
                'color' => '#10B981',
                'title_field' => 'deal_name',
                'title_label' => 'Deal Name',
                'layout' => [
                    'sections' => [
                        [
                            'title' => 'Deal Details',
                            'columns' => 2,
                            'fields' => ['deal_name', 'amount', 'stage', 'close_date'],
                        ],
                    ],
                ],
            ]);

            $fieldService->createField($dealsMod, ['label' => 'Amount', 'api_name' => 'amount', 'type' => FieldType::CURRENCY->value, 'is_required' => true]);
            $fieldService->createField($dealsMod, [
                'label' => 'Stage',
                'api_name' => 'stage',
                'type' => FieldType::PICKLIST->value,
                'options' => [
                    'values' => [
                        ['key' => 'discovery', 'label' => 'Discovery'],
                        ['key' => 'proposal', 'label' => 'Proposal'],
                        ['key' => 'closed_won', 'label' => 'Closed Won'],
                    ],
                ],
            ]);
            $fieldService->createField($dealsMod, ['label' => 'Expected Close Date', 'api_name' => 'close_date', 'type' => FieldType::DATE->value]);

            $token = $user->createToken('crm_token')->plainTextToken;

            return response()->json([
                'user' => $user->load(['organization', 'profile', 'role', 'department']),
                'token' => $token,
                'message' => 'Organization workspace created successfully.',
            ], 201);
        });
    });

    Route::post('/auth/logout', function (Request $request) {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    })->middleware('auth:sanctum');
});

Route::prefix('v1')->middleware(['auth:sanctum', SetTenantContext::class])->group(function () {
    // 1. Modules & Fields Meta
    Route::get('/modules', [ModuleMetaController::class, 'index']);
    Route::post('/modules', [ModuleMetaController::class, 'store']);
    Route::get('/modules/{apiName}', [ModuleMetaController::class, 'show']);
    Route::put('/modules/{apiName}', [ModuleMetaController::class, 'update']);
    Route::put('/modules/{apiName}/layout', [ModuleMetaController::class, 'updateLayout']);
    Route::post('/modules/{apiName}/fields', [ModuleMetaController::class, 'storeField']);
    Route::put('/modules/{apiName}/fields/{fieldId}', [ModuleMetaController::class, 'updateField']);

    // 2. Generic CRM Records Engine (Dynamic CRUD for any module)
    Route::get('/modules/{moduleApi}/records', [GenericRecordController::class, 'index']);
    Route::post('/modules/{moduleApi}/records', [GenericRecordController::class, 'store']);
    Route::get('/modules/{moduleApi}/records/{id}', [GenericRecordController::class, 'show']);
    Route::put('/modules/{moduleApi}/records/{id}', [GenericRecordController::class, 'update']);
    Route::patch('/modules/{moduleApi}/records/{id}', [GenericRecordController::class, 'update']);
    Route::delete('/modules/{moduleApi}/records/{id}', [GenericRecordController::class, 'destroy']);
    Route::post('/modules/{moduleApi}/records/mass-update', [GenericRecordController::class, 'massUpdate']);
    Route::post('/modules/{moduleApi}/records/mass-delete', [GenericRecordController::class, 'massDelete']);
    Route::post('/modules/{moduleApi}/records/{id}/owner', [GenericRecordController::class, 'changeOwner']);

    // 3. Custom Views & Saved Filters
    Route::get('/modules/{moduleApi}/views', [CustomViewController::class, 'indexViews']);
    Route::post('/modules/{moduleApi}/views', [CustomViewController::class, 'storeView']);
    Route::get('/modules/{moduleApi}/filters', [CustomViewController::class, 'indexFilters']);
    Route::post('/modules/{moduleApi}/filters', [CustomViewController::class, 'storeFilter']);

    // 4. Permissions Matrix & Sharing Rules
    Route::get('/permissions/matrix', [PermissionController::class, 'getMatrix']);
    Route::put('/permissions/profiles/{profileId}/modules', [PermissionController::class, 'updateProfileModulePermissions']);
    Route::put('/permissions/sharing', [PermissionController::class, 'updateSharingPermission']);
    Route::put('/permissions/profiles/{profileId}/fields', [PermissionController::class, 'updateFieldPermission']);

    // 5. User & Team Management
    Route::get('/users/meta', [UserController::class, 'getMetadata']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
