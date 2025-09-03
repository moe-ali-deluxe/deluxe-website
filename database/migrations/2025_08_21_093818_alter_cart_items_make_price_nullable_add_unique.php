<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // If you need to change column nullability, install doctrine/dbal:
        // composer require doctrine/dbal
        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change(); // allow null until checkout
            $table->unsignedInteger('quantity')->default(1)->change(); // safer unsigned
        });

        // Ensure a single row per (cart, product)
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id']);
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->integer('quantity')->default(1)->change();
        });
    }
};
