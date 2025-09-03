<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'discount_price',
        'stock',
        'brand_id',
        'category_id',
        'vendor_id',
        'meta_title',
        'meta_description',
        'is_active',
        'featured',
        'is_new',
        'rating',
        'barcode',
    ];

    protected $casts = [
        'price'           => 'float',
        'discount_price'  => 'float',
        'stock'           => 'int',
        'is_active'       => 'bool',
        'featured'        => 'bool',
        'is_new'          => 'bool',
        'rating'          => 'float',
    ];

    // Expose computed price when casting to array/json (optional)
    protected $appends = ['display_price'];

    /* ----------------------------- Relationships ----------------------------- */

    public function category(): BelongsTo
    {
       return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function images(): HasMany
    {
        // If you added ProductImage::scopeOrdered(), uncomment the next line:
        // return $this->hasMany(ProductImage::class)->ordered();

        return $this->hasMany(ProductImage::class)->orderBy('id'); // safe default ordering
    }

    public function wishlistedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /* -------------------------------- Accessors ------------------------------ */

    // Prefer this everywhere in views for price display
    public function getDisplayPriceAttribute(): float
    {
        $hasDiscount = !is_null($this->discount_price) && $this->discount_price > 0;
        return (float) ($hasDiscount ? $this->discount_price : $this->price);
    }

    public function getIsInStockAttribute(): bool
    {
        return (int) $this->stock > 0;
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $path = $this->images()->value('image'); // first image path or null
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    /* ---------------------------------- Scopes ------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $q)
    {
        if (!$q) return $query;

        return $query->where(function ($qq) use ($q) {
            $qq->where('name', 'like', "%{$q}%")
               ->orWhere('sku', 'like', "%{$q}%")
               ->orWhere('description', 'like', "%{$q}%");
        });
    }

    /* ------------------------------- Lifecycle ------------------------------- */

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Product $product) {
            // Slug: create once (stable URL)
            if (blank($product->slug)) {
                $product->slug = static::makeUniqueSlug($product->name);
            } else {
                $product->slug = static::ensureUniqueSanitizedSlug($product->slug, null, $product->name);
            }

            if (empty($product->barcode)) {
                $product->barcode = self::generateBarcode();
            }
            self::ensureMeta($product);
        });

        static::updating(function (Product $product) {
            // If user edited slug, sanitize + ensure unique. If they didn't, keep current slug (stability).
            if ($product->isDirty('slug')) {
                $product->slug = static::ensureUniqueSanitizedSlug($product->slug, $product->id, $product->name);
            } elseif (blank($product->slug)) {
                // Edge case: slug got blanked programmatically
                $product->slug = static::makeUniqueSlug($product->name, $product->id);
            }

            self::ensureMeta($product);
        });
    }

    /* --------------------------------- Helpers ------------------------------- */

    public static function generateBarcode(): string
    {
        return str_pad((string) mt_rand(1, 9999999999999), 13, '0', STR_PAD_LEFT);
    }

    /**
     * Ensure meta fields are auto-filled if left blank (never overwrites non-empty).
     */
    protected static function ensureMeta(Product $p): void
    {
        if (blank($p->meta_title)) {
            $p->meta_title = self::makeMetaTitle($p->name ?? '');
        }

        if (blank($p->meta_description)) {
            $source = $p->description ?: ($p->name ?? '');
            $p->meta_description = self::makeMetaDescription($source);
        }
    }

    public static function makeMetaTitle(string $name): string
    {
        // Trim and cap ~60 chars
        return Str::limit(trim($name), 60, '');
    }

    public static function makeMetaDescription(?string $text): string
    {
        // Strip HTML, collapse whitespace, cap ~160 chars
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
        return Str::limit($plain, 160, '');
    }

    /**
     * Create a unique slug from a name. If $ignoreId is given, ignore that row (for updates).
     */
    public static function makeUniqueSlug(string $name, $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Sanitize a provided slug and ensure it's unique; if empty, fall back to name.
     */
    protected static function ensureUniqueSanitizedSlug(?string $slug, $ignoreId = null, ?string $fallbackName = null): string
    {
        $slug = Str::slug((string) $slug);

        if (blank($slug)) {
            return static::makeUniqueSlug((string) $fallbackName, $ignoreId);
        }

        // If the sanitized slug already exists, suffix with -n
        if (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {

            $base = $slug;
            $i = 1;
            while (static::where('slug', $base . '-' . $i)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()) {
                $i++;
            }
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    /* -------------------------- Inventory model note ------------------------- */
    /**
     * You decrement the `stock` column during checkout (and log StockMovement),
     * so `stock` is the source of truth for availability. Avoid mixing it with a
     * computed sum of movements in business logic.
     */
}
