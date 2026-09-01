<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->jsonb('data')->default('{}');
            $table->boolean('is_locked')->default(false);
            $table->string('locked_reason')->nullable();
            $table->integer('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'module_id', 'deleted_at']);
            $table->index(['organization_id', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_records');
    }
};
