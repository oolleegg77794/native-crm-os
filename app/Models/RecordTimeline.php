<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordTimeline extends Model
{
    use HasFactory;

    protected $table = 'record_timeline';

    protected $fillable = [
        'record_id',
        'module_id',
        'user_id',
        'event_type',
        'changes',
        'description',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(CrmRecord::class, 'record_id');
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
