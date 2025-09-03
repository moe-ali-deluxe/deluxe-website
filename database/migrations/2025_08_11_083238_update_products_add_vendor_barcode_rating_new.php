<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('rating', 2, 1)->default(0.0);
            $table->boolean('is_new')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'barcode', 'rating', 'is_new']);
        });
    }
};
