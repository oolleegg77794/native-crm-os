<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\DynamicEngine\DynamicRecordService;
use App\Core\Meta\FieldMetaService;
use App\Core\Meta\ModuleMetaService;
use App\Enums\FieldType;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Organization
        $org = Organization::firstOrCreate([
            'slug' => 'acme',
        ], [
            'name' => 'Acme Global Corp',
            'is_active' => true,
        ]);

        // 2. Create Profiles
        $adminProfile = Profile::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Administrator',
        ], [
            'description' => 'Full administrative access to all modules and configurations',
            'is_admin' => true,
        ]);

        $standardProfile = Profile::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Standard User',
        ], [
            'description' => 'Standard CRM operational access',
            'is_admin' => false,
        ]);

        // 3. Create Role Hierarchy Tree
        $ceoRole = Role::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Chief Executive Officer (CEO)',
        ]);

        $salesDirectorRole = Role::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Sales Director',
        ], [
            'parent_id' => $ceoRole->id,
        ]);

        $salesManagerRole = Role::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Sales Manager',
        ], [
            'parent_id' => $salesDirectorRole->id,
        ]);

        $salesRepRole = Role::firstOrCreate([
            'organization_id' => $org->id,
            'name' => 'Sales Representative',
        ], [
            'parent_id' => $salesManagerRole->id,
        ]);

        // 4. Create Default Admin User
        $adminUser = User::firstOrCreate([
            'email' => 'admin@crm.local',
        ], [
            'organization_id' => $org->id,
            'profile_id' => $adminProfile->id,
            'role_id' => $ceoRole->id,
            'name' => 'Oleg Admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Also create a sales rep user for testing role hierarchy
        User::firstOrCreate([
            'email' => 'rep@crm.local',
        ], [
            'organization_id' => $org->id,
            'profile_id' => $standardProfile->id,
            'role_id' => $salesRepRole->id,
            'name' => 'Sarah Sales',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // 5. Initialize Services
        $metaService = app(ModuleMetaService::class);
        $fieldService = app(FieldMetaService::class);
        $recordService = app(DynamicRecordService::class);

        // 6. Create "Leads" Module
        $leadsMod = $metaService->findByApiName($org->id, 'leads');
        if (! $leadsMod) {
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
                            'fields' => ['full_name', 'company', 'email', 'phone', 'lead_source', 'status'],
                        ],
                        [
                            'title' => 'Financial Info',
                            'columns' => 2,
                            'fields' => ['annual_revenue'],
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
                        ['key' => 'unqualified', 'label' => 'Unqualified'],
                    ],
                ],
            ]);
            $fieldService->createField($leadsMod, [
                'label' => 'Annual Revenue',
                'api_name' => 'annual_revenue',
                'type' => FieldType::CURRENCY->value,
            ]);

            // Sample Lead Records
            $recordService->create($adminUser, $leadsMod, [
                'full_name' => 'Alexander Wright',
                'company' => 'Apex Dynamics LLC',
                'email' => 'a.wright@apexdynamics.com',
                'phone' => '+1 (555) 234-5678',
                'status' => 'qualified',
                'annual_revenue' => 1250000,
            ]);

            $recordService->create($adminUser, $leadsMod, [
                'full_name' => 'Elena Rostova',
                'company' => 'Vanguard Financial',
                'email' => 'elena@vanguardfin.io',
                'phone' => '+1 (555) 876-5432',
                'status' => 'new',
                'annual_revenue' => 450000,
            ]);
        }

        // 7. Create "Deals" Module
        $dealsMod = $metaService->findByApiName($org->id, 'deals');
        if (! $dealsMod) {
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
                            'title' => 'Opportunity Info',
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
                        ['key' => 'proposal', 'label' => 'Proposal Sent'],
                        ['key' => 'negotiation', 'label' => 'Negotiation'],
                        ['key' => 'closed_won', 'label' => 'Closed Won'],
                        ['key' => 'closed_lost', 'label' => 'Closed Lost'],
                    ],
                ],
            ]);
            $fieldService->createField($dealsMod, ['label' => 'Expected Close Date', 'api_name' => 'close_date', 'type' => FieldType::DATE->value]);

            // Sample Deals
            $recordService->create($adminUser, $dealsMod, [
                'deal_name' => 'Enterprise Cloud Migration',
                'amount' => 75000,
                'stage' => 'proposal',
                'close_date' => '2026-10-15',
            ]);

            $recordService->create($adminUser, $dealsMod, [
                'deal_name' => 'Security Infrastructure Audit',
                'amount' => 32000,
                'stage' => 'closed_won',
                'close_date' => '2026-09-30',
            ]);
        }
    }
}
