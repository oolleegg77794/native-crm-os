<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FieldAccessLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileFieldPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'field_id',
        'access_level',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FieldMeta::class, 'field_id');
    }

    public function accessLevelEnum(): FieldAccessLevel
    {
        return FieldAccessLevel::tryFrom($this->access_level) ?? FieldAccessLevel::WRITE;
    }
}
