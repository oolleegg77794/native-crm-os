<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatedList extends Model
{
    use HasFactory;

    protected $table = 'related_lists';

    protected $fillable = [
        'module_id',
        'related_module_id',
        'lookup_field',
        'display_name',
        'sort_order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleMeta::class, 'module_id');
    }

    public function relatedModule(): BelongsTo
    {
        return $this->belongsTo(ModuleMeta::class, 'related_module_id');
    }
}
