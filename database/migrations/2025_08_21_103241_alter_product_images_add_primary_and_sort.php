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
       Schema::table('product_images', function (Blueprint $table) {
    $table->string('alt')->nullable()->after('image');
    $table->boolean('is_primary')->default(false)->after('alt');
    $table->unsignedInteger('sort_order')->default(0)->after('is_primary');
    $table->index(['product_id', 'is_primary', 'sort_order']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            //
        });
    }
};
