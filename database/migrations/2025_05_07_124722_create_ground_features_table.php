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
        Schema::create('ground_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ground_id')->constrained('grounds');
            $table->string('feature_name');
            $table->string('feature_type');
            $table->string('feature_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ground_features');
    }
};
