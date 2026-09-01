<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SharingAccessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharingPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'module_id',
        'access_type',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleMeta::class, 'module_id');
    }

    public function accessTypeEnum(): SharingAccessType
    {
        return SharingAccessType::tryFrom($this->access_type) ?? SharingAccessType::PUBLIC_READ_WRITE;
    }
}
