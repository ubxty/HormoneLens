<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Disease catalog ─────────────────────────────
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();           // 'diabetes', 'pcod', 'thyroid', …
            $table->string('name');                       // 'Type-2 Diabetes'
            $table->string('icon')->default('🩺');        // emoji or icon class
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            // JSON map of risk-engine weights for this disease
            // e.g. {"metabolic": 0.4, "insulin": 0.3, "hormonal": 0.3}
            $table->json('risk_weights')->nullable();

            $table->timestamps();
        });

        // ─── Field definitions per disease ───────────────
        Schema::create('disease_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
            $table->string('slug');                       // 'avg_blood_sugar'
            $table->string('label');                      // 'Average Blood Sugar (mg/dL)'
            $table->string('field_type');                 // 'number', 'boolean', 'select', 'text'
            $table->string('category')->default('general'); // grouping: 'vitals', 'symptoms', 'history'

            // JSON: options for select, range for number, etc.
            // e.g. {"options":["often","occasionally","rarely"]}  or  {"min":50,"max":500}
            $table->json('options')->nullable();

            // JSON Laravel validation rules
            // e.g. {"rules":["required","numeric","min:50","max:500"]}
            $table->json('validation')->nullable();

            // How much this field contributes to risk (0-100 scale weight)
            // and which score it affects
            // e.g. {"score":"metabolic","weight":20,"condition":{"operator":">","value":200}}
            $table->json('risk_config')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['disease_id', 'slug']);
        });

        // ─── User's disease data (one row per user-disease) ──
        Schema::create('user_disease_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();

            // All field values stored as JSON: {"avg_blood_sugar":180,"family_history":true,...}
            $table->json('field_values')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'disease_id']);
        });

        // ─── Drop old hardcoded tables ───────────────────
        Schema::dropIfExists('disease_diabetes');
        Schema::dropIfExists('disease_pcod');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_disease_data');
        Schema::dropIfExists('disease_fields');
        Schema::dropIfExists('diseases');

        // Re-create old tables in down() for rollback
        Schema::create('disease_diabetes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('avg_blood_sugar', 6, 2)->nullable();
            $table->boolean('family_history')->default(false);
            $table->string('frequent_urination')->default('rarely');
            $table->string('excessive_thirst')->default('rarely');
            $table->string('fatigue')->default('rarely');
            $table->string('blurred_vision')->default('rarely');
            $table->boolean('numbness_tingling')->default(false);
            $table->boolean('slow_wound_healing')->default(false);
            $table->boolean('unexplained_weight_loss')->default(false);
            $table->string('sugar_cravings')->default('rare');
            $table->boolean('energy_crashes_after_meals')->default(false);
            $table->timestamps();
        });

        Schema::create('disease_pcod', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('cycle_regularity')->default('regular');
            $table->unsignedSmallInteger('avg_cycle_length_days')->nullable();
            $table->boolean('excess_facial_body_hair')->default(false);
            $table->boolean('acne_oily_skin')->default(false);
            $table->boolean('hair_thinning')->default(false);
            $table->boolean('weight_gain_difficulty_losing')->default(false);
            $table->boolean('mood_swings_anxiety')->default(false);
            $table->boolean('dark_skin_patches')->default(false);
            $table->string('fatigue_frequency')->default('rarely');
            $table->string('sleep_disturbances')->default('rarely');
            $table->string('sugar_cravings')->default('rare');
            $table->boolean('insulin_resistance_diagnosed')->default(false);
            $table->timestamps();
        });
    }
};
