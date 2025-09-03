<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

class ViewServiceProvider extends ServiceProvider
{
   public function register()
    {
        //
    }

    public function boot()
    {
        // Share categories with all views
        View::composer('*', function ($view) {
            $categories = \App\Models\Category::whereNull('parent_id')->with('children')->get();
            $view->with('categories', $categories);
        });
    }

}
