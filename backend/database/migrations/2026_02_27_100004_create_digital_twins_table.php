<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_twins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('metabolic_health_score', 5, 2)->default(0);
            $table->decimal('insulin_resistance_score', 5, 2)->default(0);
            $table->decimal('sleep_score', 5, 2)->default(0);
            $table->decimal('stress_score', 5, 2)->default(0);
            $table->decimal('diet_score', 5, 2)->default(0);
            $table->decimal('overall_risk_score', 5, 2)->default(0);
            $table->enum('risk_category', ['low', 'moderate', 'high', 'critical'])->default('low');
            $table->json('snapshot_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_twins');
    }
};
