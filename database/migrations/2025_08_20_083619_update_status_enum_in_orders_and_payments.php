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
        \DB::table('orders')
            ->whereNotIn('status', ['pending','completed','cancelled'])
            ->update(['status' => 'pending']);

        \DB::table('payments')
            ->whereNotIn('status', ['pending','completed','cancelled'])
            ->update(['status' => 'pending']);

        // Then, modify ENUMs
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'partially_paid', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'partially_paid', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending','completed','cancelled'])
                  ->default('pending')
                  ->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending','completed','cancelled'])
                  ->default('pending')
                  ->change();
        });
    }
};
