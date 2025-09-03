<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'total_amount', 'status'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
     // Convenience: sum quantities
    public function getItemsCountAttribute(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
