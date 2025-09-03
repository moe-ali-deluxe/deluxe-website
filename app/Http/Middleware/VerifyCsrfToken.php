<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // You can add URIs to exclude from CSRF protection here
    protected $except = [
        //
    ];
}
