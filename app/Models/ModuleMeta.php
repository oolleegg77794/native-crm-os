<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleMeta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'module_meta';

    protected $fillable = [
        'organization_id',
        'name',
        'singular_name',
        'api_name',
        'icon',
        'color',
        'title_field',
        'is_system',
        'is_active',
        'layout',
        'options',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'layout' => 'array',
        'options' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FieldMeta::class, 'module_id')->orderBy('sort_order');
    }

    public function records(): HasMany
    {
        return $this->hasMany(CrmRecord::class, 'module_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ProfilePermission::class, 'module_id');
    }

    public function sharingPermission(): HasOne
    {
        return $this->hasOne(SharingPermission::class, 'module_id');
    }

    public function customViews(): HasMany
    {
        return $this->hasMany(ModuleCustomView::class, 'module_id');
    }

    public function savedFilters(): HasMany
    {
        return $this->hasMany(SavedFilter::class, 'module_id');
    }

    public function relatedLists(): HasMany
    {
        return $this->hasMany(RelatedList::class, 'module_id')->orderBy('sort_order');
    }

    public function getField(string $apiName): ?FieldMeta
    {
        return $this->fields->firstWhere('api_name', $apiName);
    }

    /**
     * @return Collection<int, FieldMeta>
     */
    public function getLookupFields(): Collection
    {
        return $this->fields->filter(fn (FieldMeta $field) => $field->typeEnum()->isLookup());
    }
}
