<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the price_per_slot column to ground_slots
        Schema::table('ground_slots', function (Blueprint $table) {
            $table->decimal('price_per_slot', 10, 2)->after('slot_status')->nullable();
        });

        // Copy price_per_hour from grounds to price_per_slot in ground_slots
        DB::statement('
            UPDATE ground_slots
            SET price_per_slot = (
                SELECT price_per_hour
                FROM grounds
                WHERE grounds.id = ground_slots.ground_id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ground_slots', function (Blueprint $table) {
            $table->dropColumn('price_per_slot');
        });
    }
};
