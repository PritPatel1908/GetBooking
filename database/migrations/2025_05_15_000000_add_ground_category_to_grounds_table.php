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
        Schema::table('grounds', function (Blueprint $table) {
            if (!Schema::hasColumn('grounds', 'ground_category')) {
                $table->string('ground_category')->default('allgrounds')->after('ground_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grounds', function (Blueprint $table) {
            if (Schema::hasColumn('grounds', 'ground_category')) {
                $table->dropColumn('ground_category');
            }
        });
    }
};
