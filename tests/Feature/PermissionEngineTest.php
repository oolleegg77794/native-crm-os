<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DynamicEngine\DynamicRecordRepository;
use App\Core\DynamicEngine\DynamicRecordService;
use App\Core\Meta\FieldMetaService;
use App\Core\Meta\ModuleMetaService;
use App\Core\Permissions\PermissionEngine;
use App\Enums\FieldAccessLevel;
use App\Enums\FieldType;
use App\Enums\SharingAccessType;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\ProfileFieldPermission;
use App\Models\Role;
use App\Models\SharingPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected ModuleMetaService $metaService;
    protected FieldMetaService $fieldService;
    protected DynamicRecordService $recordService;
    protected DynamicRecordRepository $repository;
    protected PermissionEngine $permissionEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create(['name' => 'Security Corp', 'slug' => 'sec-corp']);
        $this->metaService = app(ModuleMetaService::class);
        $this->fieldService = app(FieldMetaService::class);
        $this->recordService = app(DynamicRecordService::class);
        $this->repository = app(DynamicRecordRepository::class);
        $this->permissionEngine = app(PermissionEngine::class);
    }

    public function test_role_hierarchy_private_data_sharing(): void
    {
        // 1. Setup Role Tree: CEO -> Sales Manager -> Sales Rep A & Sales Rep B
        $standardProfile = Profile::create([
            'organization_id' => $this->org->id,
            'name' => 'Standard User',
            'is_admin' => false,
        ]);

        $managerRole = Role::create(['organization_id' => $this->org->id, 'name' => 'Sales Manager']);
        $repRole = Role::create(['organization_id' => $this->org->id, 'name' => 'Sales Rep', 'parent_id' => $managerRole->id]);

        $managerUser = User::factory()->create([
            'organization_id' => $this->org->id,
            'profile_id' => $standardProfile->id,
            'role_id' => $managerRole->id,
        ]);

        $repUserA = User::factory()->create([
            'organization_id' => $this->org->id,
            'profile_id' => $standardProfile->id,
            'role_id' => $repRole->id,
        ]);

        $repUserB = User::factory()->create([
            'organization_id' => $this->org->id,
            'profile_id' => $standardProfile->id,
            'role_id' => $repRole->id,
        ]);

        // 2. Create Module "Opportunities" with PRIVATE sharing
        $oppsModule = $this->metaService->createModule($this->org->id, [
            'name' => 'Opportunities',
            'api_name' => 'opportunities',
            'title_field' => 'name',
        ]);

        SharingPermission::updateOrCreate([
            'organization_id' => $this->org->id,
            'module_id' => $oppsModule->id,
        ], [
            'access_type' => SharingAccessType::PRIVATE->value,
        ]);

        // 3. Rep A creates an Opportunity
        $oppRecord = $this->recordService->create($repUserA, $oppsModule, [
            'name' => 'Big Deal Rep A',
        ]);

        // 4. Assert Rep A can access
        $this->assertTrue($this->permissionEngine->canAccessRecord($repUserA, $oppRecord, 'view'));

        // 5. Assert Manager can access (via Role Hierarchy)
        $this->assertTrue($this->permissionEngine->canAccessRecord($managerUser, $oppRecord, 'view'));

        // 6. Assert Rep B CANNOT access Rep A's private record
        $this->assertFalse($this->permissionEngine->canAccessRecord($repUserB, $oppRecord, 'view'));
    }

    public function test_field_level_security_hiding_confidential_fields(): void
    {
        $profile = Profile::create([
            'organization_id' => $this->org->id,
            'name' => 'Restricted User',
            'is_admin' => false,
        ]);

        $user = User::factory()->create([
            'organization_id' => $this->org->id,
            'profile_id' => $profile->id,
        ]);

        $module = $this->metaService->createModule($this->org->id, [
            'name' => 'Employees',
            'api_name' => 'employees',
            'title_field' => 'full_name',
        ]);

        $salaryField = $this->fieldService->createField($module, [
            'label' => 'Salary',
            'api_name' => 'salary',
            'type' => FieldType::CURRENCY->value,
        ]);

        // Set FLS: Salary field is HIDDEN for Restricted User Profile
        ProfileFieldPermission::create([
            'profile_id' => $profile->id,
            'field_id' => $salaryField->id,
            'access_level' => FieldAccessLevel::HIDDEN->value,
        ]);

        // Create record (via Admin)
        $adminProfile = Profile::create(['organization_id' => $this->org->id, 'name' => 'Admin', 'is_admin' => true]);
        $admin = User::factory()->create(['organization_id' => $this->org->id, 'profile_id' => $adminProfile->id]);

        $record = $this->recordService->create($admin, $module, [
            'full_name' => 'John Doe',
            'salary' => 150000,
        ]);

        // User queries record
        $sanitized = $this->repository->findById($user, $module, $record->id);

        $this->assertNotNull($sanitized);
        $this->assertArrayNotHasKey('salary', $sanitized['fields']);
        $this->assertArrayNotHasKey('salary', $sanitized['raw_values']);
        $this->assertArrayHasKey('full_name', $sanitized['fields']);
    }
}
