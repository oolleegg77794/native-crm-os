<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_id',
        'user_id',
        'title',
        'content',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(CrmRecord::class, 'record_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
