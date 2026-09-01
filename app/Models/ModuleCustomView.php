<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleCustomView extends Model
{
    use HasFactory;

    protected $table = 'module_custom_views';

    protected $fillable = [
        'organization_id',
        'module_id',
        'user_id',
        'name',
        'columns',
        'sort_by',
        'sort_direction',
        'is_default',
        'is_system',
    ];

    protected $casts = [
        'columns' => 'array',
        'is_default' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleMeta::class, 'module_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
