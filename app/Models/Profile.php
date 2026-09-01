<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ProfilePermission::class);
    }

    public function fieldPermissions(): HasMany
    {
        return $this->hasMany(ProfileFieldPermission::class);
    }
}
