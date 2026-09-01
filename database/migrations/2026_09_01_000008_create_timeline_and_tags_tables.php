<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('crm_records')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('module_meta')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type'); // create, update, delete, restore, change_owner, lock, unlock
            $table->jsonb('changes')->nullable(); // {"old": {...}, "new": {...}}
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['record_id', 'created_at']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#6B7280');
            $table->timestamps();

            $table->unique(['organization_id', 'name']);
        });

        Schema::create('record_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('crm_records')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['record_id', 'tag_id']);
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('crm_records')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['record_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
        Schema::dropIfExists('record_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('record_timeline');
    }
};
