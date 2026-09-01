<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldMeta extends Model
{
    use HasFactory;

    protected $table = 'field_meta';

    protected $fillable = [
        'module_id',
        'api_name',
        'label',
        'type',
        'is_required',
        'is_unique',
        'is_readonly',
        'default_value',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_unique' => 'boolean',
        'is_readonly' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleMeta::class, 'module_id');
    }

    public function fieldPermissions(): HasMany
    {
        return $this->hasMany(ProfileFieldPermission::class, 'field_id');
    }

    public function typeEnum(): FieldType
    {
        return FieldType::tryFrom($this->type) ?? FieldType::STRING;
    }
}
