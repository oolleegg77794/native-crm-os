<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_custom_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); // null = public org view
            $table->string('name');
            $table->jsonb('columns'); // array of field api_names e.g. ["name", "amount", "stage", "created_at"]
            $table->string('sort_by')->nullable();
            $table->string('sort_direction')->default('desc');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['organization_id', 'module_id', 'user_id']);
        });

        Schema::create('saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->jsonb('conditions'); // array of rules: [{"field": "amount", "operator": ">=", "value": 1000}]
            $table->string('logic')->default('and'); // and / or
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['organization_id', 'module_id', 'user_id']);
        });

        Schema::create('related_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->foreignId('related_module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->string('lookup_field'); // field in related module that points to this module
            $table->string('display_name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_lists');
        Schema::dropIfExists('saved_filters');
        Schema::dropIfExists('module_custom_views');
    }
};
