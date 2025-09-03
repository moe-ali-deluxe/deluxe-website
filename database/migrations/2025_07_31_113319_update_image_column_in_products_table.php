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
            if (Schema::hasColumn('products', 'image_path')) {
                $table->dropColumn('image_path');
            }

            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable();
            }

            if (Schema::hasColumn('products', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
