<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'website',
        'address',
        'notes',
        'logo',
    ];
}
