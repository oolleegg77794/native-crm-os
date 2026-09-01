<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\DynamicEngine\DynamicRecordRepository;
use App\Core\DynamicEngine\DynamicRecordService;
use App\Core\Meta\FieldMetaService;
use App\Core\Meta\ModuleMetaService;
use App\Enums\FieldType;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicRecordEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $user;
    protected ModuleMetaService $metaService;
    protected FieldMetaService $fieldService;
    protected DynamicRecordService $recordService;
    protected DynamicRecordRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create(['name' => 'Tech Corp', 'slug' => 'tech-corp']);
        $profile = Profile::create(['organization_id' => $this->org->id, 'name' => 'Admin', 'is_admin' => true]);
        $this->user = User::factory()->create([
            'organization_id' => $this->org->id,
            'profile_id' => $profile->id,
        ]);

        $this->metaService = app(ModuleMetaService::class);
        $this->fieldService = app(FieldMetaService::class);
        $this->recordService = app(DynamicRecordService::class);
        $this->repository = app(DynamicRecordRepository::class);
    }

    public function test_can_create_and_query_jsonb_records_with_filters(): void
    {
        // 1. Create Module "Leads"
        $leadsModule = $this->metaService->createModule($this->org->id, [
            'name' => 'Leads',
            'api_name' => 'leads',
            'title_field' => 'full_name',
        ]);

        $this->fieldService->createField($leadsModule, [
            'label' => 'Score',
            'api_name' => 'score',
            'type' => FieldType::INTEGER->value,
        ]);

        $this->fieldService->createField($leadsModule, [
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

        // 2. Create records
        $record1 = $this->recordService->create($this->user, $leadsModule, [
            'full_name' => 'Alice Johnson',
            'score' => 95,
            'status' => 'qualified',
        ]);

        $record2 = $this->recordService->create($this->user, $leadsModule, [
            'full_name' => 'Bob Smith',
            'score' => 40,
            'status' => 'new',
        ]);

        // 3. Query with filter (score >= 50)
        $paginator = $this->repository->paginate($this->user, $leadsModule, [
            'filters' => [
                ['field' => 'score', 'operator' => '>=', 'value' => 50],
            ],
        ]);

        $this->assertEquals(1, $paginator->total());
        $this->assertEquals('Alice Johnson', $paginator->items()[0]['raw_values']['full_name']);
    }

    public function test_lookup_field_resolution_batching(): void
    {
        // 1. Accounts module
        $accountsModule = $this->metaService->createModule($this->org->id, [
            'name' => 'Accounts',
            'api_name' => 'accounts',
            'title_field' => 'company_name',
        ]);

        $accountRecord = $this->recordService->create($this->user, $accountsModule, [
            'company_name' => 'Stark Industries',
        ]);

        // 2. Deals module with Lookup to Accounts
        $dealsModule = $this->metaService->createModule($this->org->id, [
            'name' => 'Deals',
            'api_name' => 'deals',
            'title_field' => 'deal_name',
        ]);

        $this->fieldService->createField($dealsModule, [
            'label' => 'Account',
            'api_name' => 'account_id',
            'type' => FieldType::LOOKUP->value,
            'options' => [
                'target_module' => 'accounts',
            ],
        ]);

        // 3. Create Deal record pointing to Account
        $dealRecord = $this->recordService->create($this->user, $dealsModule, [
            'deal_name' => 'Arc Reactor Supply',
            'account_id' => $accountRecord->id,
        ]);

        // 4. Query deal and assert lookup resolution
        $result = $this->repository->findById($this->user, $dealsModule, $dealRecord->id);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('lookups', $result);
        $this->assertEquals([
            'id' => $accountRecord->id,
            'label' => 'Stark Industries',
        ], $result['lookups']['account_id']);
    }
}
