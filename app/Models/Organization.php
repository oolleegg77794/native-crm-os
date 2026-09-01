<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ModuleMeta::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(CrmRecord::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
