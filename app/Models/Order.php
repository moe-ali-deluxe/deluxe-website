<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_method',
        'shipping_address',
        'voucher_code',
    ];

    const STATUSES = ['pending', 'partially_paid', 'completed', 'cancelled'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate remaining balance for this order.
     *
     * @param  int|null  $excludePaymentId  Optional payment ID to exclude (used when editing a payment).
     * @return float
     */
    public function remainingBalance($excludePaymentId = null)
    {
        // Start a fresh query for payments
        $query = $this->payments();

        if ($excludePaymentId) {
            $query->where('id', '!=', $excludePaymentId);
        }

        $paid = $query->sum('amount');

        // Use bcsub for precise subtraction and avoid negative numbers
        return max(0, (float) bcsub($this->total_amount, $paid, 2));
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
