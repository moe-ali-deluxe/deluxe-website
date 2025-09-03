<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    const METHODS = ['Cash', 'WishMoney', 'OMT'];
    const STATUSES = ['pending', 'partially_paid', 'completed', 'cancelled'];
    
    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime', // <-- ensures paid_at is a Carbon instance
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
