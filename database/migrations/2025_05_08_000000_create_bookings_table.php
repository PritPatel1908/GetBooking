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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_sku')->unique()->nullable();
            $table->foreignId('ground_id')->constrained('grounds');
            $table->foreignId('user_id')->constrained('users');
            $table->date('booking_date');
            $table->string('booking_time');
            $table->integer('duration')->comment('Duration in hours');
            $table->decimal('amount', 10, 2);
            $table->enum('booking_status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
