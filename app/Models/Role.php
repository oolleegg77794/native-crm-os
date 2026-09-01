<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'parent_id',
        'description',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all descendants (subordinate roles) recursively.
     *
     * @return Collection<int, Role>
     */
    public function descendants(): Collection
    {
        $descendants = new Collection();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Get array of all descendant role IDs (including current if requested).
     *
     * @return list<int>
     */
    public function descendantIds(bool $includeSelf = false): array
    {
        $ids = $this->descendants()->pluck('id')->all();

        if ($includeSelf) {
            array_unshift($ids, $this->id);
        }

        return $ids;
    }

    /**
     * Get all ancestors (manager roles) up to root.
     *
     * @return Collection<int, Role>
     */
    public function ancestors(): Collection
    {
        $ancestors = new Collection();
        $current = $this->parent;

        while ($current !== null) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Check if this role is a superior (ancestor) of another role.
     */
    public function isAncestorOf(Role $role): bool
    {
        return in_array($role->id, $this->descendantIds(false), true);
    }
}
