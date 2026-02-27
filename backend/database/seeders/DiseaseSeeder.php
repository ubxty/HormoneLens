<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\DiseaseField;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = $this->getDiseaseDefinitions();

        foreach ($diseases as $def) {
            $disease = Disease::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'icon' => $def['icon'],
                    'description' => $def['description'],
                    'is_active' => $def['is_active'] ?? true,
                    'sort_order' => $def['sort_order'] ?? 0,
                    'risk_weights' => $def['risk_weights'],
                ],
            );

            foreach ($def['fields'] as $i => $field) {
                DiseaseField::updateOrCreate(
                    ['disease_id' => $disease->id, 'slug' => $field['slug']],
                    [
                        'label' => $field['label'],
                        'field_type' => $field['field_type'],
                        'category' => $field['category'] ?? 'general',
                        'options' => $field['options'] ?? null,
                        'validation' => $field['validation'] ?? null,
                        'risk_config' => $field['risk_config'] ?? null,
                        'sort_order' => $field['sort_order'] ?? $i,
                        'is_required' => $field['is_required'] ?? false,
                    ],
                );
            }
        }
    }

    private function getDiseaseDefinitions(): array
    {
        return [
            // ═══════════════════════════════════════════════
            // 1. TYPE-2 DIABETES
            // ═══════════════════════════════════════════════
            [
                'slug' => 'diabetes',
                'name' => 'Type-2 Diabetes',
                'icon' => '🩸',
                'description' => 'Track blood sugar levels, symptoms, and diabetes-specific risk factors for metabolic health management.',
                'sort_order' => 1,
                'risk_weights' => [
                    'metabolic' => 0.45,
                    'insulin' => 0.35,
                    'hormonal' => 0.20,
                ],
                'fields' => [
                    // ── Vitals ──
                    [
                        'slug' => 'avg_blood_sugar',
                        'label' => 'Average Blood Sugar (mg/dL)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'is_required' => true,
                        'options' => ['min' => 50, 'max' => 500, 'step' => 1],
                        'validation' => ['rules' => ['required', 'numeric', 'min:50', 'max:500']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '>', 'value' => 200, 'impact' => 20],
                                ['operator' => '>', 'value' => 140, 'impact' => 10],
                            ],
                        ],
                        'sort_order' => 0,
                    ],
                    // ── History ──
                    [
                        'slug' => 'family_history',
                        'label' => 'Family History of Diabetes',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'options' => null,
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '==', 'value' => true, 'impact' => 5],
                            ],
                        ],
                        'sort_order' => 1,
                    ],
                    // ── Symptoms (frequency) ──
                    [
                        'slug' => 'frequent_urination',
                        'label' => 'Frequent Urination',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '==', 'value' => 'often', 'impact' => 5],
                            ],
                        ],
                        'sort_order' => 2,
                    ],
                    [
                        'slug' => 'excessive_thirst',
                        'label' => 'Excessive Thirst',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '==', 'value' => 'often', 'impact' => 5],
                            ],
                        ],
                        'sort_order' => 3,
                    ],
                    [
                        'slug' => 'fatigue',
                        'label' => 'Fatigue Level',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [
                                ['operator' => '==', 'value' => 'often', 'impact' => 10],
                            ],
                        ],
                        'sort_order' => 4,
                    ],
                    [
                        'slug' => 'blurred_vision',
                        'label' => 'Blurred Vision',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '==', 'value' => 'often', 'impact' => 5],
                            ],
                        ],
                        'sort_order' => 5,
                    ],
                    // ── Symptoms (boolean) ──
                    [
                        'slug' => 'numbness_tingling',
                        'label' => 'Numbness or Tingling in Hands/Feet',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 6,
                    ],
                    [
                        'slug' => 'slow_wound_healing',
                        'label' => 'Slow Wound Healing',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 7,
                    ],
                    [
                        'slug' => 'unexplained_weight_loss',
                        'label' => 'Unexplained Weight Loss',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 8,
                    ],
                    [
                        'slug' => 'sugar_cravings',
                        'label' => 'Sugar Cravings',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['frequent', 'occasional', 'rare']],
                        'validation' => ['rules' => ['in:frequent,occasional,rare']],
                        'risk_config' => [
                            'score' => 'insulin',
                            'rules' => [
                                ['operator' => '==', 'value' => 'frequent', 'impact' => 10],
                                ['operator' => '==', 'value' => 'occasional', 'impact' => 3],
                            ],
                        ],
                        'sort_order' => 9,
                    ],
                    [
                        'slug' => 'energy_crashes_after_meals',
                        'label' => 'Energy Crashes After Meals',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 10,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════
            // 2. PCOD / PCOS
            // ═══════════════════════════════════════════════
            [
                'slug' => 'pcod',
                'name' => 'PCOD / PCOS',
                'icon' => '🧬',
                'description' => 'Track menstrual cycle, hormonal symptoms, and PCOD-specific risk factors.',
                'sort_order' => 2,
                'risk_weights' => [
                    'metabolic' => 0.25,
                    'insulin' => 0.30,
                    'hormonal' => 0.45,
                ],
                'fields' => [
                    // ── Cycle ──
                    [
                        'slug' => 'cycle_regularity',
                        'label' => 'Menstrual Cycle Regularity',
                        'field_type' => 'select',
                        'category' => 'cycle',
                        'is_required' => true,
                        'options' => ['options' => ['regular', 'irregular', 'missed']],
                        'validation' => ['rules' => ['required', 'in:regular,irregular,missed']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [
                                ['operator' => '==', 'value' => 'missed', 'impact' => 15],
                                ['operator' => '==', 'value' => 'irregular', 'impact' => 10],
                            ],
                        ],
                        'sort_order' => 0,
                    ],
                    [
                        'slug' => 'avg_cycle_length_days',
                        'label' => 'Average Cycle Length (days)',
                        'field_type' => 'number',
                        'category' => 'cycle',
                        'options' => ['min' => 15, 'max' => 90],
                        'validation' => ['rules' => ['nullable', 'integer', 'min:15', 'max:90']],
                        'sort_order' => 1,
                    ],
                    // ── Symptoms (boolean) ──
                    [
                        'slug' => 'excess_facial_body_hair',
                        'label' => 'Excess Facial/Body Hair (Hirsutism)',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 2,
                    ],
                    [
                        'slug' => 'acne_oily_skin',
                        'label' => 'Acne / Oily Skin',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 3,
                    ],
                    [
                        'slug' => 'hair_thinning',
                        'label' => 'Hair Thinning / Loss',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 4,
                    ],
                    [
                        'slug' => 'weight_gain_difficulty_losing',
                        'label' => 'Weight Gain / Difficulty Losing Weight',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 5,
                    ],
                    [
                        'slug' => 'mood_swings_anxiety',
                        'label' => 'Mood Swings / Anxiety',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 6,
                    ],
                    [
                        'slug' => 'dark_skin_patches',
                        'label' => 'Dark Skin Patches (Acanthosis Nigricans)',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'insulin',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 7,
                    ],
                    // ── Symptoms (frequency) ──
                    [
                        'slug' => 'fatigue_frequency',
                        'label' => 'Fatigue Frequency',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => 'often', 'impact' => 5]],
                        ],
                        'sort_order' => 8,
                    ],
                    [
                        'slug' => 'sleep_disturbances',
                        'label' => 'Sleep Disturbances',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => 'often', 'impact' => 10]],
                        ],
                        'sort_order' => 9,
                    ],
                    [
                        'slug' => 'sugar_cravings',
                        'label' => 'Sugar Cravings',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['frequent', 'occasional', 'rare']],
                        'validation' => ['rules' => ['in:frequent,occasional,rare']],
                        'risk_config' => [
                            'score' => 'insulin',
                            'rules' => [
                                ['operator' => '==', 'value' => 'frequent', 'impact' => 10],
                                ['operator' => '==', 'value' => 'occasional', 'impact' => 3],
                            ],
                        ],
                        'sort_order' => 10,
                    ],
                    [
                        'slug' => 'insulin_resistance_diagnosed',
                        'label' => 'Diagnosed with Insulin Resistance',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'risk_config' => [
                            'score' => 'insulin',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 25]],
                        ],
                        'sort_order' => 11,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════
            // 3. THYROID DISORDERS
            // ═══════════════════════════════════════════════
            [
                'slug' => 'thyroid',
                'name' => 'Thyroid Disorders',
                'icon' => '🦋',
                'description' => 'Track thyroid function, hormonal balance, and related metabolic symptoms.',
                'sort_order' => 3,
                'risk_weights' => [
                    'metabolic' => 0.30,
                    'insulin' => 0.20,
                    'hormonal' => 0.50,
                ],
                'fields' => [
                    [
                        'slug' => 'tsh_level',
                        'label' => 'TSH Level (mIU/L)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'is_required' => true,
                        'options' => ['min' => 0.1, 'max' => 100, 'step' => 0.01],
                        'validation' => ['rules' => ['required', 'numeric', 'min:0.1', 'max:100']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [
                                ['operator' => '>', 'value' => 10, 'impact' => 20],
                                ['operator' => '>', 'value' => 4.5, 'impact' => 10],
                                ['operator' => '<', 'value' => 0.4, 'impact' => 15],
                            ],
                        ],
                        'sort_order' => 0,
                    ],
                    [
                        'slug' => 't4_level',
                        'label' => 'T4 Level (µg/dL)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'options' => ['min' => 0.1, 'max' => 25, 'step' => 0.1],
                        'validation' => ['rules' => ['nullable', 'numeric', 'min:0.1', 'max:25']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [
                                ['operator' => '<', 'value' => 4.5, 'impact' => 15],
                                ['operator' => '>', 'value' => 12, 'impact' => 15],
                            ],
                        ],
                        'sort_order' => 1,
                    ],
                    [
                        'slug' => 'thyroid_type',
                        'label' => 'Type of Thyroid Condition',
                        'field_type' => 'select',
                        'category' => 'history',
                        'is_required' => true,
                        'options' => ['options' => ['hypothyroid', 'hyperthyroid', 'hashimotos', 'graves', 'unsure']],
                        'validation' => ['rules' => ['required', 'in:hypothyroid,hyperthyroid,hashimotos,graves,unsure']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [
                                ['operator' => '==', 'value' => 'hashimotos', 'impact' => 15],
                                ['operator' => '==', 'value' => 'graves', 'impact' => 15],
                            ],
                        ],
                        'sort_order' => 2,
                    ],
                    [
                        'slug' => 'on_medication',
                        'label' => 'Currently on Thyroid Medication',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'sort_order' => 3,
                    ],
                    [
                        'slug' => 'family_history',
                        'label' => 'Family History of Thyroid Disease',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 4,
                    ],
                    [
                        'slug' => 'weight_change',
                        'label' => 'Unexplained Weight Changes',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['significant_gain', 'slight_gain', 'stable', 'slight_loss', 'significant_loss']],
                        'validation' => ['rules' => ['in:significant_gain,slight_gain,stable,slight_loss,significant_loss']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '==', 'value' => 'significant_gain', 'impact' => 10],
                                ['operator' => '==', 'value' => 'significant_loss', 'impact' => 10],
                            ],
                        ],
                        'sort_order' => 5,
                    ],
                    [
                        'slug' => 'fatigue',
                        'label' => 'Fatigue / Low Energy',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => 'often', 'impact' => 10]],
                        ],
                        'sort_order' => 6,
                    ],
                    [
                        'slug' => 'cold_intolerance',
                        'label' => 'Cold Intolerance',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 7,
                    ],
                    [
                        'slug' => 'dry_skin_hair',
                        'label' => 'Dry Skin / Brittle Hair',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 8,
                    ],
                    [
                        'slug' => 'heart_palpitations',
                        'label' => 'Heart Palpitations / Rapid Heartbeat',
                        'field_type' => 'boolean',
                        'category' => 'symptoms',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 9,
                    ],
                    [
                        'slug' => 'mood_changes',
                        'label' => 'Mood Changes / Depression',
                        'field_type' => 'select',
                        'category' => 'symptoms',
                        'options' => ['options' => ['often', 'occasionally', 'rarely']],
                        'validation' => ['rules' => ['in:often,occasionally,rarely']],
                        'risk_config' => [
                            'score' => 'hormonal',
                            'rules' => [['operator' => '==', 'value' => 'often', 'impact' => 10]],
                        ],
                        'sort_order' => 10,
                    ],
                ],
            ],

            // ═══════════════════════════════════════════════
            // 4. METABOLIC SYNDROME
            // ═══════════════════════════════════════════════
            [
                'slug' => 'metabolic-syndrome',
                'name' => 'Metabolic Syndrome',
                'icon' => '⚖️',
                'description' => 'Track the cluster of conditions — high blood pressure, high blood sugar, excess body fat, and abnormal cholesterol — that increase cardiovascular risk.',
                'sort_order' => 4,
                'risk_weights' => [
                    'metabolic' => 0.50,
                    'insulin' => 0.30,
                    'hormonal' => 0.20,
                ],
                'fields' => [
                    [
                        'slug' => 'waist_circumference',
                        'label' => 'Waist Circumference (cm)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'is_required' => true,
                        'options' => ['min' => 50, 'max' => 200, 'step' => 0.5],
                        'validation' => ['rules' => ['required', 'numeric', 'min:50', 'max:200']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '>', 'value' => 102, 'impact' => 15, 'note' => 'male threshold'],
                                ['operator' => '>', 'value' => 88, 'impact' => 10, 'note' => 'female threshold'],
                            ],
                        ],
                        'sort_order' => 0,
                    ],
                    [
                        'slug' => 'fasting_blood_sugar',
                        'label' => 'Fasting Blood Sugar (mg/dL)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'is_required' => true,
                        'options' => ['min' => 50, 'max' => 400],
                        'validation' => ['rules' => ['required', 'numeric', 'min:50', 'max:400']],
                        'risk_config' => [
                            'score' => 'insulin',
                            'rules' => [
                                ['operator' => '>', 'value' => 126, 'impact' => 20],
                                ['operator' => '>', 'value' => 100, 'impact' => 10],
                            ],
                        ],
                        'sort_order' => 1,
                    ],
                    [
                        'slug' => 'systolic_bp',
                        'label' => 'Systolic Blood Pressure (mmHg)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'options' => ['min' => 60, 'max' => 250],
                        'validation' => ['rules' => ['nullable', 'numeric', 'min:60', 'max:250']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '>', 'value' => 140, 'impact' => 15],
                                ['operator' => '>', 'value' => 130, 'impact' => 8],
                            ],
                        ],
                        'sort_order' => 2,
                    ],
                    [
                        'slug' => 'diastolic_bp',
                        'label' => 'Diastolic Blood Pressure (mmHg)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'options' => ['min' => 40, 'max' => 150],
                        'validation' => ['rules' => ['nullable', 'numeric', 'min:40', 'max:150']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '>', 'value' => 90, 'impact' => 10],
                                ['operator' => '>', 'value' => 85, 'impact' => 5],
                            ],
                        ],
                        'sort_order' => 3,
                    ],
                    [
                        'slug' => 'triglycerides',
                        'label' => 'Triglycerides (mg/dL)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'options' => ['min' => 20, 'max' => 1000],
                        'validation' => ['rules' => ['nullable', 'numeric', 'min:20', 'max:1000']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '>', 'value' => 200, 'impact' => 15],
                                ['operator' => '>', 'value' => 150, 'impact' => 8],
                            ],
                        ],
                        'sort_order' => 4,
                    ],
                    [
                        'slug' => 'hdl_cholesterol',
                        'label' => 'HDL Cholesterol (mg/dL)',
                        'field_type' => 'number',
                        'category' => 'vitals',
                        'options' => ['min' => 10, 'max' => 150],
                        'validation' => ['rules' => ['nullable', 'numeric', 'min:10', 'max:150']],
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [
                                ['operator' => '<', 'value' => 40, 'impact' => 15],
                                ['operator' => '<', 'value' => 50, 'impact' => 8],
                            ],
                        ],
                        'sort_order' => 5,
                    ],
                    [
                        'slug' => 'on_bp_medication',
                        'label' => 'Currently on Blood Pressure Medication',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 6,
                    ],
                    [
                        'slug' => 'on_cholesterol_medication',
                        'label' => 'Currently on Cholesterol Medication',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 5]],
                        ],
                        'sort_order' => 7,
                    ],
                    [
                        'slug' => 'family_history_heart',
                        'label' => 'Family History of Heart Disease',
                        'field_type' => 'boolean',
                        'category' => 'history',
                        'risk_config' => [
                            'score' => 'metabolic',
                            'rules' => [['operator' => '==', 'value' => true, 'impact' => 10]],
                        ],
                        'sort_order' => 8,
                    ],
                ],
            ],
        ];
    }
}
