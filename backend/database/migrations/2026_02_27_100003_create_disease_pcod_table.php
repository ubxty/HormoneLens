<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_pcod', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('cycle_regularity', ['regular', 'irregular', 'missed'])->default('regular');
            $table->unsignedSmallInteger('avg_cycle_length_days')->nullable();
            $table->boolean('excess_facial_body_hair')->default(false);
            $table->boolean('acne_oily_skin')->default(false);
            $table->boolean('hair_thinning')->default(false);
            $table->boolean('weight_gain_difficulty_losing')->default(false);
            $table->boolean('mood_swings_anxiety')->default(false);
            $table->boolean('dark_skin_patches')->default(false);
            $table->enum('fatigue_frequency', ['often', 'occasionally', 'rarely'])->default('rarely');
            $table->enum('sleep_disturbances', ['often', 'occasionally', 'rarely'])->default('rarely');
            $table->enum('sugar_cravings', ['frequent', 'occasional', 'rare'])->default('rare');
            $table->boolean('insulin_resistance_diagnosed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_pcod');
    }
};
