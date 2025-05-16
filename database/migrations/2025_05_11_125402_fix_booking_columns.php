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
        Schema::table('booking_details', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_details', 'slot_id')) {
                $table->foreignId('slot_id')->nullable()->constrained('ground_slots')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_details', function (Blueprint $table) {
            if (Schema::hasColumn('booking_details', 'slot_id')) {
                $table->dropForeign(['slot_id']);
                $table->dropColumn('slot_id');
            }
        });
    }
};
