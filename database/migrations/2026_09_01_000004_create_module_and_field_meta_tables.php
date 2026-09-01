<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name'); // e.g. "Deals"
            $table->string('singular_name'); // e.g. "Deal"
            $table->string('api_name'); // e.g. "deals"
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('title_field')->default('name'); // The field used as record title/display
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->jsonb('layout')->nullable(); // Sections, columns, field positions
            $table->jsonb('options')->nullable(); // Auto-number, default sorting, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'api_name']);
        });

        Schema::create('field_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->string('api_name'); // e.g. "amount", "lead_source"
            $table->string('label'); // e.g. "Deal Amount", "Lead Source"
            $table->string('type'); // string, integer, decimal, picklist, lookup, etc.
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->jsonb('options')->nullable(); // Picklist values, lookup target module, currency code, validation rules
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'api_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_meta');
        Schema::dropIfExists('module_meta');
    }
};
