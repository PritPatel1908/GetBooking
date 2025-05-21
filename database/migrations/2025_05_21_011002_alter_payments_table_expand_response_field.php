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
            // Change payment_response from VARCHAR to TEXT to allow longer JSON responses
            $table->text('payment_response')->change();
            $table->text('payment_response_data')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Change back to VARCHAR (although data might be truncated)
            $table->string('payment_response')->change();
            $table->string('payment_response_data')->change();
        });
    }
};
