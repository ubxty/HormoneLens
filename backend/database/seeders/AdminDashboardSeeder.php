<?php

namespace Database\Seeders;

use App\Enums\AlertType;
use App\Enums\PhysicalActivity;
use App\Enums\RiskCategory;
use App\Enums\Severity;
use App\Enums\SimulationType;
use App\Enums\StressLevel;
use App\Models\Alert;
use App\Models\DigitalTwin;
use App\Models\HealthProfile;
use App\Models\Simulation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDashboardSeeder extends Seeder
{
    private const NAMES = [
        'Ayesha Khan', 'Ravi Mehta', 'Sneha Patel', 'Arjun Nair', 'Divya Reddy',
        'Karan Singh', 'Meera Iyer', 'Rohan Das', 'Pooja Gupta', 'Vikram Sharma',
        'Nisha Verma', 'Amit Joshi', 'Lakshmi Rao', 'Suresh Pillai', 'Ananya Roy',
        'Deepak Tiwari', 'Kavitha Menon', 'Sanjay Malhotra', 'Rekha Nambiar', 'Tarun Bose',
        'Farida Sheikh', 'Harish Kumar', 'Sowmya Srinivas', 'Prakash Yadav', 'Jyoti Pandey',
        'Ashwin Chandra', 'Pramila Chatterjee', 'Girish Kulkarni', 'Nalini Goswami', 'Vivek Bhatt',
    ];

    private const DISEASE_TYPES = ['diabetes', 'thyroid', 'pcos', 'obesity', 'hypertension'];

    private const SIM_EXPLANATIONS = [
        'meal' => [
            'Replacing refined carbs with complex carbs is projected to reduce insulin spikes by 30–40%. Continuous glucose monitoring shows a direct correlation between meal glycaemic index and postprandial inflammation.',
            'A Mediterranean-style meal pattern shows strong evidence for improving insulin sensitivity. High-fibre foods slow glucose absorption, reducing metabolic stress on the pancreas.',
            'High-GI meal detected. Pairing carbohydrates with protein and healthy fats significantly blunts postprandial blood sugar response and reduces HbA1c burden over time.',
        ],
        'sleep' => [
            'Extending sleep from 5 to 7.5 hours is projected to reduce cortisol spikes by 22% and improve insulin sensitivity. Circadian rhythm alignment is critical for hormonal balance.',
            'Deep sleep deprivation elevates ghrelin and suppresses leptin, directly driving metabolic dysfunction. Improving sleep architecture can yield a 15% reduction in overall risk score.',
            'Sleep fragmentation detected. Consistent sleep onset and wake times strengthen cortisol rhythm, improve glucose tolerance, and reduce inflammatory markers such as IL-6.',
        ],
        'stress' => [
            'Chronic stress elevates cortisol, which directly antagonises insulin signalling. Mindfulness-based stress reduction has shown a 28% improvement in metabolic markers over 8 weeks.',
            'Reducing perceived stress from high to moderate is projected to lower HbA1c by 0.4 points. The hypothalamic–pituitary–adrenal axis dysregulation is a major driver of insulin resistance.',
            'Stress-driven cortisol surges are contributing to visceral fat accumulation. Structured relaxation therapy and adaptogens show evidence for restoring HPA axis balance.',
        ],
        'food_impact' => [
            'This food item triggers a high glycaemic response. Substituting with a lower-GI alternative projected to reduce postprandial glucose peak by 45 mg/dL within the same meal context.',
            'Food analysis complete. High saturated fat content may contribute to insulin resistance over long-term consumption. Consider omega-3 rich alternatives to improve lipid profile.',
            'Processed food detected. Artificial sweeteners can disrupt gut microbiome diversity, which is linked to metabolic syndrome. Whole-food substitution recommended.',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding admin dashboard data...');

        $users = $this->createUsers();
        $this->createDigitalTwinsAndSimulations($users);
        $this->createAlerts($users);
        $this->createRecentActivity($users);

        $this->command->info('Admin dashboard seeded: ' . count($users) . ' demo users, digital twins, simulations, and alerts created.');
    }

    private function createUsers(): array
    {
        $users = [];
        $usedEmails = [];

        foreach (self::NAMES as $i => $name) {
            $parts = explode(' ', strtolower($name));
            $base  = $parts[0] . '.' . ($parts[1] ?? $i);
            $email = $base . '@demo.hl';

            // Ensure uniqueness
            if (in_array($email, $usedEmails)) {
                $email = $base . $i . '@demo.hl';
            }
            $usedEmails[] = $email;

            $createdAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23));

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'       => $name,
                    'password'   => Hash::make('password'),
                    'is_admin'   => false,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            $disease = self::DISEASE_TYPES[$i % count(self::DISEASE_TYPES)];

            HealthProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'weight'            => round(rand(52, 105) + (rand(0, 9) / 10), 1),
                    'height'            => round(rand(150, 185) + (rand(0, 9) / 10), 1),
                    'avg_sleep_hours'   => round(rand(4, 9) + (rand(0, 5) / 10), 1),
                    'stress_level'      => collect(StressLevel::cases())->random()->value,
                    'physical_activity' => collect(PhysicalActivity::cases())->random()->value,
                    'eating_habits'     => $this->randomEatingHabit($disease),
                    'water_intake'      => round(rand(12, 35) / 10, 1),
                    'disease_type'      => $disease,
                ]
            );

            $users[] = $user;
        }

        return $users;
    }

    private function createDigitalTwinsAndSimulations(array $users): void
    {
        // Risk weights: more low/moderate than critical to mimic real distribution
        $riskBuckets = [
            ['min' => 10, 'max' => 30, 'cat' => RiskCategory::LOW,      'weight' => 8],
            ['min' => 31, 'max' => 55, 'cat' => RiskCategory::MODERATE,  'weight' => 12],
            ['min' => 56, 'max' => 75, 'cat' => RiskCategory::HIGH,      'weight' => 7],
            ['min' => 76, 'max' => 95, 'cat' => RiskCategory::CRITICAL,  'weight' => 3],
        ];

        $bucketList = [];
        foreach ($riskBuckets as $b) {
            for ($w = 0; $w < $b['weight']; $w++) {
                $bucketList[] = $b;
            }
        }
        shuffle($bucketList);

        foreach ($users as $idx => $user) {
            $bucket       = $bucketList[$idx % count($bucketList)];
            $overallRisk  = rand($bucket['min'] * 10, $bucket['max'] * 10) / 10;

            $twin = DigitalTwin::updateOrCreate(
                ['user_id' => $user->id, 'is_active' => true],
                [
                    'metabolic_health_score'   => round(rand(30, 90) / 10, 2),
                    'insulin_resistance_score' => round(rand(20, 85) / 10, 2),
                    'sleep_score'              => round(rand(25, 90) / 10, 2),
                    'stress_score'             => round(rand(20, 80) / 10, 2),
                    'diet_score'               => round(rand(25, 90) / 10, 2),
                    'overall_risk_score'       => $overallRisk,
                    'risk_category'            => $bucket['cat'],
                    'snapshot_data'            => [
                        'health_profile' => [
                            'disease_type'    => self::DISEASE_TYPES[$idx % count(self::DISEASE_TYPES)],
                            'avg_sleep_hours' => rand(4, 9),
                            'stress_level'    => collect(['low', 'moderate', 'high'])->random(),
                            'eating_habits'   => 'seeded',
                        ],
                    ],
                    'is_active' => true,
                ]
            );

            // 2–6 simulations spread over the last 30 days
            $simCount = rand(2, 6);
            $types = SimulationType::cases();

            for ($s = 0; $s < $simCount; $s++) {
                $type          = $types[$s % count($types)];
                $origRisk      = $overallRisk + rand(-8, 8);
                $origRisk      = max(5, min(95, $origRisk));
                $simRisk       = $origRisk + rand(-12, 12);
                $simRisk       = max(5, min(95, $simRisk));
                $explanations  = self::SIM_EXPLANATIONS[$type->value] ?? ['Simulation complete.'];
                $simCreatedAt  = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));

                Simulation::create([
                    'user_id'              => $user->id,
                    'digital_twin_id'      => $twin->id,
                    'type'                 => $type,
                    'input_data'           => ['type' => $type->value, 'description' => 'Seeded scenario'],
                    'modified_twin_data'   => ['modified' => true],
                    'original_risk_score'  => round($origRisk, 2),
                    'simulated_risk_score' => round($simRisk, 2),
                    'risk_change'          => round($simRisk - $origRisk, 2),
                    'risk_category_before' => RiskCategory::fromScore($origRisk),
                    'risk_category_after'  => RiskCategory::fromScore($simRisk),
                    'rag_explanation'      => $explanations[array_rand($explanations)],
                    'rag_confidence'       => round(rand(70, 98) / 100, 2),
                    'results'              => ['scores' => [], 'reasoning_path' => []],
                    'created_at'           => $simCreatedAt,
                    'updated_at'           => $simCreatedAt,
                ]);
            }
        }
    }

    private function createAlerts(array $users): void
    {
        $alertTemplates = [
            [
                'type'     => AlertType::RISK_THRESHOLD,
                'severity' => Severity::CRITICAL,
                'title'    => 'Critical Risk Score Detected',
                'message'  => 'Overall metabolic risk score has exceeded the critical threshold (>75). Immediate lifestyle intervention is strongly recommended.',
            ],
            [
                'type'     => AlertType::HIGH_GI,
                'severity' => Severity::WARNING,
                'title'    => 'High Glycaemic Index Meal Detected',
                'message'  => 'Recent meal simulation indicates a high glycaemic load. Repeated exposure increases HbA1c and insulin resistance over time.',
            ],
            [
                'type'     => AlertType::LOW_SLEEP,
                'severity' => Severity::WARNING,
                'title'    => 'Chronic Sleep Deficit',
                'message'  => 'Average sleep below 6 hours reported. Sleep deprivation is a primary driver of cortisol dysregulation and metabolic dysfunction.',
            ],
            [
                'type'     => AlertType::HIGH_STRESS,
                'severity' => Severity::WARNING,
                'title'    => 'Elevated Stress Indicators',
                'message'  => 'Stress score is in the high range. Chronic stress elevates cortisol, directly antagonising insulin signalling and fat metabolism.',
            ],
            [
                'type'     => AlertType::REPEATED_RISK,
                'severity' => Severity::INFO,
                'title'    => 'Persistent Risk Pattern',
                'message'  => 'Risk score has remained in the high category across the last 3 simulations. Review lifestyle factors and consider consulting a specialist.',
            ],
            [
                'type'     => AlertType::RISK_THRESHOLD,
                'severity' => Severity::INFO,
                'title'    => 'Risk Score Improving',
                'message'  => 'Simulation shows a 12% reduction in risk score following dietary adjustments. Continue current intervention plan.',
            ],
        ];

        foreach ($users as $i => $user) {
            // Each user gets 1–3 alerts
            $numAlerts = rand(1, 3);
            for ($a = 0; $a < $numAlerts; $a++) {
                $tpl = $alertTemplates[($i + $a) % count($alertTemplates)];

                Alert::create([
                    'user_id'     => $user->id,
                    'type'        => $tpl['type'],
                    'severity'    => $tpl['severity'],
                    'title'       => $tpl['title'],
                    'message'     => $tpl['message'],
                    'is_read'     => (bool) rand(0, 1), // roughly half read
                    'created_at'  => Carbon::now()->subHours(rand(1, 240)),
                    'updated_at'  => Carbon::now()->subHours(rand(0, 10)),
                ]);
            }
        }
    }

    // Extra recent simulations concentrated in the last 7 days to make the chart interesting
    private function createRecentActivity(array $users): void
    {
        $types = SimulationType::cases();

        for ($day = 0; $day < 7; $day++) {
            $dailyCount = rand(4, 12);
            for ($j = 0; $j < $dailyCount; $j++) {
                $user          = $users[array_rand($users)];
                $twin          = DigitalTwin::where('user_id', $user->id)->first();
                if (!$twin) continue;

                $type          = $types[array_rand($types)];
                $origRisk      = rand(200, 800) / 10;
                $simRisk       = max(5, min(95, $origRisk + rand(-15, 15)));
                $explanations  = self::SIM_EXPLANATIONS[$type->value] ?? ['Simulation complete.'];
                $ts            = Carbon::now()->subDays($day)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                Simulation::create([
                    'user_id'              => $user->id,
                    'digital_twin_id'      => $twin->id,
                    'type'                 => $type,
                    'input_data'           => ['type' => $type->value],
                    'modified_twin_data'   => ['modified' => true],
                    'original_risk_score'  => round($origRisk, 2),
                    'simulated_risk_score' => round($simRisk, 2),
                    'risk_change'          => round($simRisk - $origRisk, 2),
                    'risk_category_before' => RiskCategory::fromScore($origRisk),
                    'risk_category_after'  => RiskCategory::fromScore($simRisk),
                    'rag_explanation'      => $explanations[array_rand($explanations)],
                    'rag_confidence'       => round(rand(70, 98) / 100, 2),
                    'results'              => [],
                    'created_at'           => $ts,
                    'updated_at'           => $ts,
                ]);
            }
        }
    }

    private function randomEatingHabit(string $disease): string
    {
        $habits = [
            'diabetes'    => ['High-carb diet with frequent sweet snacks', 'Irregular meals, skips breakfast often', 'High sugar beverages, 2–3 cups of chai daily', 'Processed foods and fast food 4× a week'],
            'thyroid'     => ['Low-iodine diet, avoids seafood', 'High crucifer intake without cooking', 'Irregular meal timing affecting metabolism', 'Gluten-heavy diet with low micronutrient variety'],
            'pcos'        => ['High GI diet with refined carbohydrates', 'Dairy-heavy meals, low fibre intake', 'Late-night snacking pattern, skips breakfast', 'Processed sugar and refined flour daily'],
            'obesity'     => ['High calorie-dense foods, frequent snacking', 'Large portion sizes, low vegetable intake', 'Night eating syndrome pattern', 'High sodium and saturated fat diet'],
            'hypertension'=> ['High sodium diet, processed meats daily', 'Low potassium and magnesium intake', 'Excessive caffeine and alcohol consumption', 'Low fruit and vegetable intake'],
        ];

        $options = $habits[$disease] ?? $habits['diabetes'];
        return $options[array_rand($options)];
    }
}
