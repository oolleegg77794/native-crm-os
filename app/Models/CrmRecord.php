<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_records';

    protected $fillable = [
        'organization_id',
        'module_id',
        'owner_id',
        'created_by',
        'updated_by',
        'data',
        'is_locked',
        'locked_reason',
        'version',
    ];

    protected $casts = [
        'data' => 'array',
        'is_locked' => 'boolean',
        'version' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleMeta::class, 'module_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(RecordTimeline::class, 'record_id')->latest();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'record_tags', 'record_id', 'tag_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'record_id')->latest();
    }

    public function getFieldValue(string $key, mixed $default = null): mixed
    {
        $data = $this->data ?? [];

        return $data[$key] ?? $default;
    }

    public function setFieldValue(string $key, mixed $value): self
    {
        $data = $this->data ?? [];
        $data[$key] = $value;
        $this->data = $data;

        return $this;
    }

    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('organization_id', $orgId);
    }

    public function scopeForModule(Builder $query, int $moduleId): Builder
    {
        return $query->where('module_id', $moduleId);
    }
}
