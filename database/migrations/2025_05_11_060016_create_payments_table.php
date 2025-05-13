<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->date('date')->default(Carbon::now()->format('Y-m-d'));
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_response')->nullable();
            $table->string('payment_response_code')->nullable();
            $table->string('payment_response_data_json')->nullable();
            $table->string('payment_response_message')->nullable();
            $table->string('payment_response_data')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
