<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\HealthProfile;
use App\Models\User;
use App\Models\UserDiseaseData;
use App\Services\DigitalTwin\DigitalTwinService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPriya();
        $this->seedAnita();
        $this->seedRahul();

        // Generate digital twins (requires RiskEngine + repo via service container)
        $twinService = app(DigitalTwinService::class);

        foreach (['priya@demo.com', 'anita@demo.com', 'rahul@demo.com'] as $email) {
            $user = User::where('email', $email)->first();
            if ($user && $user->healthProfile) {
                try {
                    $twinService->generate($user);
                } catch (\Throwable $e) {
                    // Silently skip if twin generation fails
                }
            }
        }
    }

    // ─── Priya: Diabetes + Thyroid ───────────────────

    private function seedPriya(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'priya@demo.com'],
            [
                'name' => 'Priya Sharma',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        HealthProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'weight' => 72.5,
                'height' => 160,
                'avg_sleep_hours' => 6.0,
                'stress_level' => 'high',
                'physical_activity' => 'sedentary',
                'eating_habits' => 'High carb diet, frequent snacking, 2 cups chai with sugar daily',
                'water_intake' => 1.5,
                'disease_type' => 'diabetes',
            ]
        );

        // Diabetes data
        $diabetes = Disease::where('slug', 'diabetes')->first();
        if ($diabetes) {
            UserDiseaseData::updateOrCreate(
                ['user_id' => $user->id, 'disease_id' => $diabetes->id],
                ['field_values' => [
                    'avg_blood_sugar' => 210,
                    'family_history' => true,
                    'frequent_urination' => 'often',
                    'excessive_thirst' => 'often',
                    'fatigue' => 'often',
                    'blurred_vision' => 'occasionally',
                    'sugar_cravings' => 'frequent',
                    'numbness_tingling' => true,
                    'slow_wound_healing' => true,
                    'unexplained_weight_loss' => false,
                    'energy_crashes_after_meals' => true,
                ]]
            );
        }

        // Thyroid data
        $thyroid = Disease::where('slug', 'thyroid')->first();
        if ($thyroid) {
            UserDiseaseData::updateOrCreate(
                ['user_id' => $user->id, 'disease_id' => $thyroid->id],
                ['field_values' => [
                    'tsh_level' => 6.8,
                    't4_level' => 0.7,
                    'thyroid_type' => 'hypothyroid',
                    'on_medication' => true,
                    'family_history' => true,
                    'weight_change' => 'significant_gain',
                    'fatigue' => 'often',
                    'mood_changes' => 'often',
                    'cold_intolerance' => true,
                    'dry_skin_hair' => true,
                    'heart_palpitations' => false,
                ]]
            );
        }
    }

    // ─── Anita: PCOD ────────────────────────────────

    private function seedAnita(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'anita@demo.com'],
            [
                'name' => 'Anita Verma',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        HealthProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'weight' => 68.0,
                'height' => 155,
                'avg_sleep_hours' => 5.5,
                'stress_level' => 'high',
                'physical_activity' => 'moderate',
                'eating_habits' => 'Mostly home-cooked meals, occasional junk food, low protein intake',
                'water_intake' => 2.0,
                'disease_type' => 'pcod',
            ]
        );

        $pcod = Disease::where('slug', 'pcod')->first();
        if ($pcod) {
            UserDiseaseData::updateOrCreate(
                ['user_id' => $user->id, 'disease_id' => $pcod->id],
                ['field_values' => [
                    'cycle_regularity' => 'irregular',
                    'avg_cycle_length_days' => 45,
                    'excess_facial_body_hair' => true,
                    'acne_oily_skin' => true,
                    'hair_thinning' => false,
                    'weight_gain_difficulty_losing' => true,
                    'mood_swings_anxiety' => true,
                    'dark_skin_patches' => true,
                    'insulin_resistance_diagnosed' => true,
                    'fatigue_frequency' => 'often',
                    'sleep_disturbances' => 'occasionally',
                    'sugar_cravings' => 'occasional',
                ]]
            );
        }
    }

    // ─── Rahul: Metabolic Syndrome ──────────────────

    private function seedRahul(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'rahul@demo.com'],
            [
                'name' => 'Rahul Mehta',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        HealthProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'weight' => 95.0,
                'height' => 175,
                'avg_sleep_hours' => 7.0,
                'stress_level' => 'medium',
                'physical_activity' => 'sedentary',
                'eating_habits' => 'Heavy meals, lots of fried food, late-night eating, 3+ cups coffee',
                'water_intake' => 1.0,
                'disease_type' => 'metabolic-syndrome',
            ]
        );

        $metabolic = Disease::where('slug', 'metabolic-syndrome')->first();
        if ($metabolic) {
            UserDiseaseData::updateOrCreate(
                ['user_id' => $user->id, 'disease_id' => $metabolic->id],
                ['field_values' => [
                    'waist_circumference' => 108,
                    'fasting_blood_sugar' => 118,
                    'systolic_bp' => 142,
                    'diastolic_bp' => 92,
                    'triglycerides' => 220,
                    'hdl_cholesterol' => 34,
                    'on_bp_medication' => true,
                    'on_cholesterol_medication' => false,
                    'family_history_heart' => true,
                ]]
            );
        }
    }
}
