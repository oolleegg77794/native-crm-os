<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DynamicEngine\DynamicRecordService;
use App\Core\Meta\ModuleMetaService;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiTenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_a_cannot_access_tenant_b_modules_or_records_via_api(): void
    {
        // 1. Setup Tenant A
        $orgA = Organization::create(['name' => 'Org A', 'slug' => 'org-a']);
        $profileA = Profile::create(['organization_id' => $orgA->id, 'name' => 'Admin A', 'is_admin' => true]);
        $userA = User::factory()->create(['organization_id' => $orgA->id, 'profile_id' => $profileA->id]);

        // 2. Setup Tenant B
        $orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b']);
        $profileB = Profile::create(['organization_id' => $orgB->id, 'name' => 'Admin B', 'is_admin' => true]);
        $userB = User::factory()->create(['organization_id' => $orgB->id, 'profile_id' => $profileB->id]);

        $metaService = app(ModuleMetaService::class);
        $recordService = app(DynamicRecordService::class);

        // Tenant A creates module and record
        $moduleA = $metaService->createModule($orgA->id, [
            'name' => 'Projects A',
            'api_name' => 'projects_a',
            'title_field' => 'name',
        ]);

        $recordA = $recordService->create($userA, $moduleA, [
            'name' => 'Top Secret Project A',
        ]);

        // Tenant B creates module
        $moduleB = $metaService->createModule($orgB->id, [
            'name' => 'Projects B',
            'api_name' => 'projects_b',
            'title_field' => 'name',
        ]);

        // 3. User B authenticates and tries to list Org A's module
        Sanctum::actingAs($userB);

        $response = $this->getJson('/api/v1/modules/projects_a/records');
        $response->assertStatus(404);

        // User B tries to directly fetch Record A
        $responseRecord = $this->getJson("/api/v1/modules/projects_a/records/{$recordA->id}");
        $responseRecord->assertStatus(404);

        // 4. User A authenticates and accesses their own record successfully
        Sanctum::actingAs($userA);

        $responseA = $this->getJson('/api/v1/modules/projects_a/records');
        $responseA->assertStatus(200);
        $responseA->assertJsonCount(1, 'data');
    }
}
