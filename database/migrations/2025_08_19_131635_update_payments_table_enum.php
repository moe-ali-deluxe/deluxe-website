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
        Schema::table('payments', function (Blueprint $table) {
            // Change payment_method and status to ENUM
            $table->enum('payment_method', ['Cash', 'WishMoney', 'OMT'])->default('Cash')->change();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('payments', function (Blueprint $table) {
            // Revert to string if rolling back
            $table->string('payment_method')->change();
            $table->string('status')->change();
        });
    }
};
