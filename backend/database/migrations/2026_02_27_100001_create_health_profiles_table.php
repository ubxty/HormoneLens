<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->comment('kg');
            $table->decimal('height', 5, 2)->comment('cm');
            $table->decimal('avg_sleep_hours', 3, 1);
            $table->enum('stress_level', ['low', 'medium', 'high']);
            $table->enum('physical_activity', ['sedentary', 'moderate', 'active']);
            $table->text('eating_habits')->nullable();
            $table->decimal('water_intake', 4, 2)->comment('litres per day');
            $table->enum('disease_type', ['diabetes', 'pcod']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profiles');
    }
};
