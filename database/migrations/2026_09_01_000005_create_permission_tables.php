<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Profile Module-Level CRUD Permissions
        Schema::create('profile_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_import')->default(false);
            $table->timestamps();

            $table->unique(['profile_id', 'module_id']);
        });

        // 2. Organization Module-Level Sharing Permissions (Public Read/Write, Public Read-Only, Private)
        Schema::create('sharing_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->string('access_type')->default('public_read_write'); // public_read_write, public_read_only, private
            $table->timestamps();

            $table->unique(['organization_id', 'module_id']);
        });

        // 3. Profile Field-Level Security Permissions (Write, Read, Hidden)
        Schema::create('profile_field_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('field_meta')->cascadeOnDelete();
            $table->string('access_level')->default('write'); // write, read, hidden
            $table->timestamps();

            $table->unique(['profile_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_field_permissions');
        Schema::dropIfExists('sharing_permissions');
        Schema::dropIfExists('profile_permissions');
    }
};
