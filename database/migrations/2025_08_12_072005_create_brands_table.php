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
    Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('logo')->nullable(); // Store path to brand logo
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Optional: add brand_id to products table
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')
                  ->nullable()
                  ->constrained('brands')
                  ->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });

        Schema::dropIfExists('brands');
    }
};
