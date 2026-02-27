<?php

namespace Database\Seeders;

use App\Models\RagDocument;
use App\Models\RagNode;
use App\Models\RagPage;
use Illuminate\Database\Seeder;

class DiabetesRagSeeder extends Seeder
{
    public function run(): void
    {
        $doc = RagDocument::create([
            'title' => 'Diabetes Knowledge Base',
            'description' => 'Comprehensive knowledge tree for Type-2 Diabetes risk factors, symptoms, lifestyle management, and Indian dietary guidance.',
        ]);

        // ── Root nodes ────────────────────────────────
        $roots = [
            [
                'title' => 'Blood Sugar Management',
                'summary' => 'Understanding blood glucose levels, HbA1c, fasting/postprandial sugar, and how to track them.',
                'keywords' => 'blood sugar,glucose,hba1c,fasting sugar,postprandial,sugar level,hyperglycemia,hypoglycemia',
            ],
            [
                'title' => 'Insulin Resistance',
                'summary' => 'How insulin resistance develops, its relationship with metabolic syndrome, and early intervention.',
                'keywords' => 'insulin,resistance,metabolic syndrome,insulin sensitivity,prediabetes,glucose tolerance',
            ],
            [
                'title' => 'Diet & Nutrition for Diabetes',
                'summary' => 'Indian dietary recommendations, glycemic index of common foods, meal planning, and carb counting.',
                'keywords' => 'diet,nutrition,glycemic index,gi,carbs,meal plan,indian food,rice,roti,dal,sugar intake',
            ],
            [
                'title' => 'Physical Activity & Diabetes',
                'summary' => 'Exercise recommendations, impact of sedentary lifestyle on insulin, and safe workout plans.',
                'keywords' => 'exercise,physical activity,walking,yoga,sedentary,workout,fitness,movement',
            ],
            [
                'title' => 'Complications & Risk Factors',
                'summary' => 'Long-term complications of uncontrolled diabetes: neuropathy, retinopathy, nephropathy, cardiovascular risks.',
                'keywords' => 'complications,neuropathy,retinopathy,nephropathy,heart disease,kidney,eye,nerve damage,risk factor,family history',
            ],
        ];

        foreach ($roots as $rootData) {
            $root = RagNode::create([
                'document_id' => $doc->id,
                'parent_id' => null,
                'title' => $rootData['title'],
                'summary' => $rootData['summary'],
                'keywords' => $rootData['keywords'],
                'depth' => 0,
            ]);

            $this->seedChildren($doc->id, $root);
        }
    }

    private function seedChildren(int $docId, RagNode $parent): void
    {
        $childrenMap = $this->getChildrenMap();
        $parentTitle = $parent->title;

        if (!isset($childrenMap[$parentTitle])) {
            return;
        }

        foreach ($childrenMap[$parentTitle] as $childData) {
            $child = RagNode::create([
                'document_id' => $docId,
                'parent_id' => $parent->id,
                'title' => $childData['title'],
                'summary' => $childData['summary'],
                'keywords' => $childData['keywords'],
                'depth' => 1,
            ]);

            foreach ($childData['pages'] as $i => $pageContent) {
                RagPage::create([
                    'node_id' => $child->id,
                    'page_number' => $i + 1,
                    'content' => $pageContent,
                ]);
            }
        }
    }

    private function getChildrenMap(): array
    {
        return [
            'Blood Sugar Management' => [
                [
                    'title' => 'Fasting Blood Sugar',
                    'summary' => 'What is fasting blood sugar, normal ranges, and what elevated levels mean.',
                    'keywords' => 'fasting,fbs,morning sugar,empty stomach,normal range',
                    'pages' => [
                        "Fasting Blood Sugar (FBS) is measured after 8-12 hours of fasting. Normal range: 70-100 mg/dL. Prediabetes: 100-125 mg/dL. Diabetes: ≥126 mg/dL. Consistently elevated FBS indicates impaired fasting glucose, a precursor to Type-2 diabetes. Regular monitoring is essential for early detection.",
                        "Tips to manage fasting blood sugar: Avoid heavy dinners close to bedtime. Include fibre-rich foods at dinner (dal, leafy vegetables). Stay hydrated. A 15-minute post-dinner walk can reduce morning readings by 10-20 mg/dL. Indian foods like methi (fenugreek) seeds soaked overnight and consumed in the morning have shown to lower fasting glucose.",
                        "When to be concerned: If your fasting sugar consistently exceeds 110 mg/dL even with lifestyle changes, consult a physician. Keep a log of your readings. Stress and poor sleep can also elevate fasting glucose through cortisol spikes. HormoneLens tracks these interconnections through your digital twin.",
                    ],
                ],
                [
                    'title' => 'Postprandial Blood Sugar',
                    'summary' => 'Post-meal blood sugar spikes, their significance, and management.',
                    'keywords' => 'postprandial,after meal,spike,ppbs,2 hour,post meal sugar',
                    'pages' => [
                        "Postprandial blood sugar (PPBS) is measured 2 hours after a meal. Normal: <140 mg/dL. Prediabetes: 140-199 mg/dL. Diabetes: ≥200 mg/dL. Post-meal spikes are often the earliest sign of insulin resistance, sometimes appearing years before fasting glucose rises.",
                        "To control post-meal spikes: Eat in the order of vegetables first, then protein, then carbs. This simple trick can reduce glucose spikes by 30-40%. Choose low-GI alternatives: brown rice instead of white rice, whole wheat roti instead of maida. Adding curd (yogurt) to meals slows carb absorption.",
                        "High post-meal sugar leads to glycation — sugar molecules damaging blood vessels and nerves. This is why even people with 'normal' fasting sugar can develop complications if their post-meal readings are consistently high. Track both fasting and postprandial values for a complete picture.",
                    ],
                ],
                [
                    'title' => 'HbA1c & Long-term Monitoring',
                    'summary' => 'Understanding HbA1c as a 3-month average and its clinical significance.',
                    'keywords' => 'hba1c,glycated hemoglobin,3 month average,a1c,long term,monitoring',
                    'pages' => [
                        "HbA1c reflects your average blood sugar over the past 2-3 months. Normal: <5.7%. Prediabetes: 5.7-6.4%. Diabetes: ≥6.5%. Unlike daily readings, HbA1c gives the big picture and is used by doctors to assess overall glycemic control.",
                        "Every 1% reduction in HbA1c reduces risk of microvascular complications by ~35%. For most diabetics, a target of <7% is recommended. Lifestyle changes alone can reduce HbA1c by 1-2% — equivalent to adding a medication. Indian studies show that regular yoga practice reduces HbA1c by 0.5% on average.",
                    ],
                ],
            ],
            'Insulin Resistance' => [
                [
                    'title' => 'Understanding Insulin Resistance',
                    'summary' => 'How cells become resistant to insulin and why it matters.',
                    'keywords' => 'insulin resistance,mechanism,cells,receptor,glucose uptake',
                    'pages' => [
                        "Insulin resistance occurs when cells in muscles, fat, and liver don't respond well to insulin, so glucose can't easily enter cells. The pancreas compensates by producing more insulin, leading to hyperinsulinemia. Over time, the pancreas can't keep up, and blood sugar rises.",
                        "Risk factors for insulin resistance: Central obesity (waist circumference >90cm for Indian men, >80cm for women), sedentary lifestyle, family history of diabetes, PCOS in women, sleep deprivation. Indians have a genetic predisposition — the 'thrifty gene' hypothesis suggests our ancestors' adaptation to famine makes us store fat more efficiently.",
                    ],
                ],
                [
                    'title' => 'Metabolic Syndrome',
                    'summary' => 'The cluster of conditions tied to insulin resistance.',
                    'keywords' => 'metabolic syndrome,waist,triglycerides,hdl,blood pressure,cluster',
                    'pages' => [
                        "Metabolic syndrome is diagnosed when 3+ of these are present: waist >90cm (men) or >80cm (women) in Indians, triglycerides >150 mg/dL, HDL <40 (men) or <50 (women), BP >130/85, fasting glucose >100. Having metabolic syndrome doubles cardiovascular risk and increases diabetes risk 5x.",
                        "Indian-specific concern: South Asians develop metabolic syndrome at lower BMI compared to Western populations. A BMI of 23+ is considered overweight for Indians (vs 25 globally). This is why normal-weight Indians can still be 'metabolically obese' — carrying visceral fat around organs.",
                    ],
                ],
                [
                    'title' => 'Reversing Insulin Resistance',
                    'summary' => 'Evidence-based strategies to improve insulin sensitivity.',
                    'keywords' => 'reverse,improve,sensitivity,lifestyle,weight loss,exercise',
                    'pages' => [
                        "Insulin resistance can be reversed, especially in early stages. Key strategies: 1) Lose 5-7% body weight — even a 3-4 kg loss significantly improves insulin sensitivity. 2) 150 minutes/week of moderate exercise — walking 30 min/day, 5 days a week is enough. 3) Reduce refined carbs — swap white rice for millets (jowar, bajra, ragi).",
                        "Indian superfoods for insulin sensitivity: Fenugreek (methi) — contains galactomannan fibre that slows sugar absorption. Turmeric (haldi) — curcumin improves beta-cell function. Bitter gourd (karela) — contains polypeptide-p, a plant insulin. Cinnamon (dalchini) — 1g/day can improve insulin sensitivity by 10-25%.",
                        "Sleep and stress matter: Getting <6 hours of sleep reduces insulin sensitivity by 25-30% in just one week. Chronic stress elevates cortisol, which directly promotes insulin resistance. HormoneLens tracks these factors in your digital twin to show their combined impact on your metabolic health.",
                    ],
                ],
            ],
            'Diet & Nutrition for Diabetes' => [
                [
                    'title' => 'Glycemic Index of Indian Foods',
                    'summary' => 'GI values of common Indian staples and how to make better choices.',
                    'keywords' => 'glycemic index,gi,indian food,rice,roti,chapati,idli,dosa,poha',
                    'pages' => [
                        "Glycemic Index rates foods 0-100 based on how quickly they raise blood sugar. Low GI (<55): most dals, rajma, chole, oats, apples, milk. Medium GI (55-69): basmati rice, whole wheat roti, banana. High GI (>70): white rice, maida products, potato, watermelon, instant upma.",
                        "Smart Indian food swaps: White rice (GI 73) → brown rice (GI 50) or millets (GI 40-55). Maida roti → multigrain/ragi roti. Sugar in chai → stevia or reduce gradually. Fruit juice → whole fruit. Instant poha → traditional thick poha with vegetables. Adding fat/protein to high-GI meals lowers the overall glycemic response.",
                        "The plate method for Indian meals: Fill half your thali with vegetables (sabzi, salad), quarter with protein (dal, paneer, curd, chicken), quarter with carbs (roti/rice). Start meals with salad or raita. This order alone can reduce glucose spikes by 30%. Traditional Indian meals are actually well-balanced when portions are controlled.",
                    ],
                ],
                [
                    'title' => 'Meal Timing & Patterns',
                    'summary' => 'When to eat, portion control, and intermittent fasting for diabetes.',
                    'keywords' => 'meal timing,intermittent fasting,portion,snacking,breakfast,dinner time',
                    'pages' => [
                        "Consistent meal timing stabilizes blood sugar. Ideal pattern: Breakfast by 8 AM, lunch by 1 PM, dinner by 7:30 PM. Eating dinner early (before 8 PM) and maintaining a 12-hour overnight fast significantly improves fasting glucose. Late-night eating is linked to higher HbA1c.",
                        "Portion sizes matter as much as food choices. A healthy portion of rice: 1 small katori (not a heaped plate). Dal: 1-2 katori. Roti: 2 medium (not large). Healthy snack options: a handful of roasted chana, makhana (fox nuts), a small bowl of sprouts, or buttermilk (chaas). Avoid packaged 'sugar-free' snacks — they often have refined carbs.",
                    ],
                ],
                [
                    'title' => 'Sugar Cravings Management',
                    'summary' => 'Why diabetics crave sugar and evidence-based strategies to manage cravings.',
                    'keywords' => 'cravings,sugar craving,sweet tooth,dessert,mithai,control cravings',
                    'pages' => [
                        "Sugar cravings in diabetes are caused by insulin resistance itself — cells aren't getting glucose, so the brain signals 'eat more sugar'. Blood sugar crashes after spikes also trigger cravings. It's a vicious cycle. Breaking it requires addressing the root cause: insulin resistance.",
                        "Strategies: Eat protein at every meal (it stabilizes blood sugar for hours). Have a small piece of dark chocolate (>70% cocoa) instead of mithai. Try natural alternatives: dates (1-2 only, they're high in fibre), roasted makhana with a pinch of jaggery. Chromium-rich foods (broccoli, green beans) reduce sugar cravings. Regular exercise reduces cravings within 2 weeks by improving insulin sensitivity.",
                    ],
                ],
            ],
            'Physical Activity & Diabetes' => [
                [
                    'title' => 'Walking & Aerobic Exercise',
                    'summary' => 'Benefits of walking and aerobic workouts for blood sugar control.',
                    'keywords' => 'walking,aerobic,cardio,brisk walk,steps,morning walk',
                    'pages' => [
                        "Walking is the most accessible exercise for diabetes management. A 30-minute brisk walk after meals can lower post-meal blood sugar by 30-50 mg/dL. Aim for 150 minutes/week of moderate aerobic activity. Start with 10-minute walks if sedentary, gradually increasing duration.",
                        "The '10-minute post-meal walk' rule: Even a short walk after each major meal has measurable benefits. Indian studies show that post-dinner walking is especially effective for fasting glucose the next morning. Breaking sitting time every 30 minutes with 3-minute light walks improves glucose metabolism by 30%.",
                    ],
                ],
                [
                    'title' => 'Yoga & Diabetes',
                    'summary' => 'Specific yoga practices that improve insulin sensitivity and reduce stress.',
                    'keywords' => 'yoga,asana,pranayama,surya namaskar,stress relief,cortisol',
                    'pages' => [
                        "Yoga has been clinically proven to lower blood sugar and reduce HbA1c. Beneficial asanas: Surya Namaskar (whole-body insulin sensitizer), Dhanurasana (bow pose — stimulates pancreas), Paschimottanasana (seated forward bend — massages abdominal organs), Shavasana (deep relaxation — lowers cortisol).",
                        "Pranayama for diabetes: Kapalbhati (10 minutes/day) — improves pancreatic function and reduces abdominal fat. Anulom Vilom — activates parasympathetic nervous system, reducing stress hormones that raise blood sugar. Studies show 30 minutes of daily yoga reduces fasting glucose by 20-30 mg/dL within 3 months.",
                    ],
                ],
                [
                    'title' => 'Strength Training Benefits',
                    'summary' => 'How muscle-building exercises improve glucose uptake.',
                    'keywords' => 'strength,weight training,muscle,resistance exercise,gym',
                    'pages' => [
                        "Muscle is the largest glucose-consuming tissue. More muscle = better blood sugar control. Resistance training 2-3 times/week can improve insulin sensitivity by 20-30%. Even bodyweight exercises (squats, push-ups, planks) count. Muscles continue to absorb glucose for 24-48 hours after strength training.",
                        "For beginners: Start with resistance bands or bodyweight exercises. Key exercises: squats (work the largest muscle groups), planks (core stability), wall push-ups, seated rows with resistance band. 20 minutes, 3 times a week, is enough to see measurable improvement in blood sugar within 4-6 weeks.",
                    ],
                ],
            ],
            'Complications & Risk Factors' => [
                [
                    'title' => 'Diabetic Neuropathy',
                    'summary' => 'Nerve damage from high blood sugar: symptoms, prevention, and management.',
                    'keywords' => 'neuropathy,nerve damage,tingling,numbness,burning,feet,hands',
                    'pages' => [
                        "Diabetic neuropathy affects up to 50% of diabetics over time. High blood sugar damages small blood vessels that supply nerves. Symptoms: tingling, numbness, burning sensation in feet/hands, pain worse at night, loss of sensation. Peripheral neuropathy (feet/hands) is most common.",
                        "Prevention: Maintain HbA1c <7%. Check feet daily for cuts, blisters, or sores (loss of sensation means you may not feel injuries). Wear proper footwear. Vitamin B12 deficiency (common with metformin use) worsens neuropathy — get levels checked annually. Alpha-lipoic acid supplementation may help nerve function.",
                    ],
                ],
                [
                    'title' => 'Cardiovascular Risk',
                    'summary' => 'How diabetes increases heart disease risk and protective measures.',
                    'keywords' => 'heart,cardiovascular,cholesterol,bp,blood pressure,heart attack,stroke',
                    'pages' => [
                        "Diabetes doubles the risk of heart disease and stroke. High blood sugar damages blood vessel walls, promoting plaque buildup. Indians already have higher cardiovascular risk (the 'South Asian paradox' — heart attacks at younger age and lower BMI). Diabetes + Indian ethnicity = very high CVD risk.",
                        "Protective measures: Control blood sugar (HbA1c <7%), blood pressure (<130/80), and cholesterol (LDL <100). Stop smoking. Indian heart-healthy foods: walnuts, almonds (a handful/day), fish oil or flaxseeds for omega-3, garlic, and green leafy vegetables. Regular exercise and stress management through yoga/meditation are equally important.",
                    ],
                ],
                [
                    'title' => 'Family History & Genetic Risk',
                    'summary' => 'Understanding hereditary diabetes risk and proactive prevention.',
                    'keywords' => 'family history,genetic,hereditary,parent,sibling,risk factor,prevention',
                    'pages' => [
                        "Having a parent with diabetes increases your risk by 40%. If both parents have it, risk rises to 70%. Indians carry genetic variants (TCF7L2, KCNJ11) that increase susceptibility. However, genes are not destiny — environmental factors (diet, exercise, sleep) determine whether genetic risk becomes actual disease.",
                        "If you have family history: Get screened yearly starting at age 25 (earlier if overweight). Maintain healthy weight (BMI <23 for Indians). Exercise regularly — it can delay or prevent diabetes even with strong genetic risk. The Indian Diabetes Prevention Programme showed that lifestyle intervention reduced diabetes incidence by 28% in high-risk individuals.",
                    ],
                ],
            ],
        ];
    }
}
