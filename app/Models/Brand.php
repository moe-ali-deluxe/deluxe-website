<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Brand extends Model
{
        use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo', 'website', 'description'
    ];

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }
}
