<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('digital_twin_id')->constrained('digital_twins')->cascadeOnDelete();
            $table->enum('type', ['meal', 'sleep', 'stress', 'food_impact']);
            $table->json('input_data');
            $table->json('modified_twin_data')->nullable();
            $table->decimal('original_risk_score', 5, 2);
            $table->decimal('simulated_risk_score', 5, 2);
            $table->decimal('risk_change', 5, 2)->default(0);
            $table->enum('risk_category_before', ['low', 'moderate', 'high', 'critical']);
            $table->enum('risk_category_after', ['low', 'moderate', 'high', 'critical']);
            $table->text('rag_explanation')->nullable();
            $table->decimal('rag_confidence', 5, 2)->nullable();
            $table->json('results')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
