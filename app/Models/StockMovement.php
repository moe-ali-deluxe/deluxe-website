<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'movement_type',
        'reference_id',
        'description'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
