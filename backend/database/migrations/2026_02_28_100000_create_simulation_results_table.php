<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('metabolic_score', 5, 2)->default(0);
            $table->decimal('insulin_score', 5, 2)->default(0);
            $table->decimal('sleep_score', 5, 2)->default(0);
            $table->decimal('stress_score', 5, 2)->default(0);
            $table->decimal('diet_score', 5, 2)->default(0);
            $table->decimal('pcos_risk', 5, 2)->default(0);
            $table->decimal('diabetes_risk', 5, 2)->default(0);
            $table->decimal('insulin_resistance_risk', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_results');
    }
};
