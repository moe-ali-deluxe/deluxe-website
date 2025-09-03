<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id','image'];

    // (Optional) quick helper
public function getImageUrlAttribute(): ?string {
    return $this->image ? asset('storage/'.$this->image) : null;
}
    /* ---------------------------- Relationships ---------------------------- */

    /** A category belongs to an optional parent. */
    public function parent(): BelongsTo
   { return $this->belongsTo(self::class, 'parent_id'); }

    /** A category has many direct children. */
    public function children()
{
   // return $this->hasMany(self::class, 'parent_id')->with('children');
   return $this->hasMany(self::class, 'parent_id');
}

    /** Recursive load: children and their children. */
    public function childrenRecursive(): HasMany
    {
       return $this->children()
        ->withCount('productsActive')  // was: withCount('products')
        ->with(['childrenRecursive', 'parent']);
    }

    /** Convenience: grandchildren (children + nested children). */
    public function grandchildren(): HasMany
    {
        return $this->children()->with('children');
    }

    /** One category has many products. */
    public function products(): HasMany
    {
         return $this->hasMany(\App\Models\Product::class, 'category_id');
    }

    // Count only active products for this category
public function productsActive(): HasMany
{
    return $this->hasMany(\App\Models\Product::class, 'category_id')
                ->where('is_active', true);
}

    /* -------------------------------- Scopes -------------------------------- */

    /** Only root categories (no parent). */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Order by name (useful for dropdowns). */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // Sum direct + descendants (used in the table)
public function getTotalProductsCountAttribute()
{
    // prefer active counts; fallback to total
    $self = isset($this->products_active_count)
        ? (int)$this->products_active_count
        : (int)($this->products_count ?? 0);

    // use whichever relation is actually loaded
    if ($this->relationLoaded('children')) {
        $children = $this->children;
    } elseif ($this->relationLoaded('childrenRecursive')) {
        $children = $this->childrenRecursive;
    } else {
        return $self;
    }

    return $self + $children->sum(function ($c) {
        return (int)($c->total_products_count
            ?? $c->products_active_count
            ?? ($c->products_count ?? 0));
    });
}
    /* --------------------------------- Boot --------------------------------- */

    protected static function booted(): void
    {
        // Auto-generate slug on create if missing
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // If name changes and slug was never manually set, keep slug in sync (optional)
        static::updating(function (Category $category) {
            if ($category->isDirty('name') && !$category->isDirty('slug') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
