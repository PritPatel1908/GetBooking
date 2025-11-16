<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adjust FKs to avoid multiple cascade paths on SQL Server
        Schema::table('payments', function (Blueprint $table) {
            // Drop existing FK if present, ignore errors if not
            try {
                $table->dropForeign(['booking_id']);
            } catch (\Throwable $e) {
            }
            // Recreate without cascade
            $table->foreign('booking_id')->references('id')->on('bookings');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Drop existing FK if present, ignore errors if not
            try {
                $table->dropForeign(['payment_id']);
            } catch (\Throwable $e) {
            }
            // Recreate as SET NULL on delete; if SQL Server still complains, we will keep NO ACTION
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            try {
                $table->dropForeign(['payment_id']);
            } catch (\Throwable $e) {
            }
        });
        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->dropForeign(['booking_id']);
            } catch (\Throwable $e) {
            }
        });
    }
};
