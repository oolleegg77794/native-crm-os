<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedFilter extends Model
{
    use HasFactory;

    protected $table = 'saved_filters';

    protected $fillable = [
        'organization_id',
        'module_id',
        'user_id',
        'name',
        'conditions',
        'logic',
        'is_default',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_default' => 'boolean',
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
