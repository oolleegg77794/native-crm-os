<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CustomViewController;
use App\Http\Controllers\Api\V1\GenericRecordController;
use App\Http\Controllers\Api\V1\ModuleMetaController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Middleware\SetTenantContext;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/user', function (Request $request) {
    return $request->user()->load(['organization', 'profile', 'role', 'department']);
})->middleware('auth:sanctum');

// Authentication Routes
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
});
