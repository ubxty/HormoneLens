<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_diabetes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('avg_blood_sugar', 5, 1)->comment('mg/dL');
            $table->boolean('family_history')->default(false);
            $table->enum('frequent_urination', ['often', 'occasionally', 'rarely'])->default('rarely');
            $table->enum('excessive_thirst', ['often', 'occasionally', 'rarely'])->default('rarely');
            $table->enum('fatigue', ['often', 'occasionally', 'rarely'])->default('rarely');
            $table->enum('blurred_vision', ['often', 'occasionally', 'rarely'])->default('rarely');
            $table->boolean('numbness_tingling')->default(false);
            $table->boolean('slow_wound_healing')->default(false);
            $table->boolean('unexplained_weight_loss')->default(false);
            $table->enum('sugar_cravings', ['frequent', 'occasional', 'rare'])->default('rare');
            $table->boolean('energy_crashes_after_meals')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_diabetes');
    }
};
