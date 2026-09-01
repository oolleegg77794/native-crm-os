<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Meta\FieldMetaService;
use App\Core\Meta\ModuleMetaService;
use App\Enums\FieldType;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetadataEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Acme Corp',
            'slug' => 'acme',
        ]);

        $adminProfile = Profile::create([
            'organization_id' => $this->org->id,
            'name' => 'Administrator',
            'is_admin' => true,
        ]);

        $this->adminUser = User::factory()->create([
            'organization_id' => $this->org->id,
            'profile_id' => $adminProfile->id,
            'email' => 'admin@acme.com',
        ]);
    }

    public function test_can_create_module_with_default_fields_and_sharing(): void
    {
        $metaService = app(ModuleMetaService::class);

        $module = $metaService->createModule($this->org->id, [
            'name' => 'Deals',
            'singular_name' => 'Deal',
            'api_name' => 'deals',
            'icon' => 'briefcase',
            'title_field' => 'deal_name',
            'title_label' => 'Deal Name',
        ]);

        $this->assertDatabaseHas('module_meta', [
            'id' => $module->id,
            'api_name' => 'deals',
            'organization_id' => $this->org->id,
        ]);

        // Default primary field created
        $this->assertDatabaseHas('field_meta', [
            'module_id' => $module->id,
            'api_name' => 'deal_name',
            'type' => FieldType::STRING->value,
            'is_required' => true,
        ]);

        // Default sharing created
        $this->assertDatabaseHas('sharing_permissions', [
            'module_id' => $module->id,
            'access_type' => 'public_read_write',
        ]);
    }

    public function test_can_add_custom_fields_to_module(): void
    {
        $metaService = app(ModuleMetaService::class);
        $fieldService = app(FieldMetaService::class);

        $module = $metaService->createModule($this->org->id, [
            'name' => 'Contacts',
            'api_name' => 'contacts',
        ]);

        $emailField = $fieldService->createField($module, [
            'label' => 'Work Email',
            'api_name' => 'work_email',
            'type' => FieldType::EMAIL->value,
            'is_required' => false,
            'is_unique' => true,
        ]);

        $amountField = $fieldService->createField($module, [
            'label' => 'Expected Revenue',
            'api_name' => 'expected_revenue',
            'type' => FieldType::CURRENCY->value,
            'is_required' => false,
        ]);

        $this->assertEquals('work_email', $emailField->api_name);
        $this->assertEquals(FieldType::EMAIL, $emailField->typeEnum());
        $this->assertEquals('expected_revenue', $amountField->api_name);
    }
}
