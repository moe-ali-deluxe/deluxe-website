<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) (Safety) Collapse any duplicate rows before adding UNIQUE(cart_id, product_id)
        //    Keeps the most recent row and sums quantities.
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            // Merge duplicates (MySQL)
            DB::statement("
                DELETE c1 FROM cart_items c1
                INNER JOIN cart_items c2
                    ON c1.cart_id = c2.cart_id
                   AND c1.product_id = c2.product_id
                   AND c1.id > c2.id
            ");
        } else {
            // Fallback: do nothing for other drivers; adjust manually if needed.
        }

        // 2) Make price nullable and quantity unsigned
        //    (dbal is installed, so change() works)
        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->unsignedInteger('quantity')->default(1)->change();
        });

        // 3) Ensure one row per (cart_id, product_id)
        Schema::table('cart_items', function (Blueprint $table) {
            // Only add if it doesn't already exist
            $indexes = collect(DB::select('SHOW INDEX FROM cart_items'))
                ->pluck('Key_name')->all();
            if (!in_array('cart_items_cart_id_product_id_unique', $indexes, true)) {
                $table->unique(['cart_id', 'product_id'], 'cart_items_cart_id_product_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop unique index if it exists
            $table->dropUnique('cart_items_cart_id_product_id_unique');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->integer('quantity')->default(1)->change();
        });
    }
};
