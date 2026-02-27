<?php

namespace Database\Seeders;

use App\Models\RagDocument;
use App\Models\RagNode;
use App\Models\RagPage;
use Illuminate\Database\Seeder;

class PcodRagSeeder extends Seeder
{
    public function run(): void
    {
        $doc = RagDocument::create([
            'title' => 'PCOS/PCOD Knowledge Base',
            'description' => 'Comprehensive knowledge tree for Polycystic Ovary Syndrome — hormonal imbalances, symptoms, lifestyle management, and Indian-specific dietary guidance.',
        ]);

        $roots = [
            [
                'title' => 'Understanding PCOS/PCOD',
                'summary' => 'What PCOS is, its types, how it differs from PCOD, and the hormonal mechanisms involved.',
                'keywords' => 'pcos,pcod,polycystic,ovary,syndrome,hormonal imbalance,definition,types',
            ],
            [
                'title' => 'Hormonal Imbalance in PCOS',
                'summary' => 'Androgen excess, insulin-androgen connection, LH/FSH ratio, and thyroid links.',
                'keywords' => 'androgen,testosterone,lh,fsh,estrogen,progesterone,hormonal,imbalance,thyroid',
            ],
            [
                'title' => 'PCOS & Insulin Resistance',
                'summary' => 'The critical link between insulin resistance and PCOS, and why managing insulin is key.',
                'keywords' => 'insulin resistance,pcos,weight gain,metabolic,glucose,sugar,prediabetes',
            ],
            [
                'title' => 'Diet & Nutrition for PCOS',
                'summary' => 'Anti-inflammatory diet, Indian food recommendations, and supplements for PCOS.',
                'keywords' => 'diet,nutrition,anti-inflammatory,indian food,pcos diet,weight loss,supplement',
            ],
            [
                'title' => 'Lifestyle & Mental Health',
                'summary' => 'Exercise, sleep, stress management, and emotional well-being for PCOS patients.',
                'keywords' => 'exercise,sleep,stress,anxiety,depression,mental health,yoga,lifestyle,self care',
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

        if (!isset($childrenMap[$parent->title])) {
            return;
        }

        foreach ($childrenMap[$parent->title] as $childData) {
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
            'Understanding PCOS/PCOD' => [
                [
                    'title' => 'PCOS vs PCOD',
                    'summary' => 'Clinical differences between PCOS and PCOD.',
                    'keywords' => 'pcos,pcod,difference,polycystic ovary disease,syndrome',
                    'pages' => [
                        "PCOD (Polycystic Ovary Disease) and PCOS (Polycystic Ovary Syndrome) are often used interchangeably but differ in severity. PCOD is a condition where ovaries release many immature eggs that turn into cysts. PCOS is a metabolic disorder with hormonal imbalance affecting multiple body systems. PCOS is more severe and can lead to diabetes, heart disease, and infertility.",
                        "In India, 1 in 5 women of reproductive age have PCOS/PCOD. Many remain undiagnosed because symptoms like irregular periods and acne are normalized. The Rotterdam criteria for PCOS diagnosis requires 2 of 3: irregular/absent ovulation, clinical/biochemical hyperandrogenism, polycystic ovaries on ultrasound.",
                    ],
                ],
                [
                    'title' => 'Types of PCOS',
                    'summary' => 'Four types of PCOS based on root cause.',
                    'keywords' => 'types,insulin resistant,adrenal,inflammatory,post pill,classification',
                    'pages' => [
                        "Four types of PCOS: 1) Insulin-Resistant PCOS (most common, ~70%) — driven by high insulin causing ovaries to produce excess androgens. Weight gain, sugar cravings, fatigue. 2) Adrenal PCOS — stress-driven, elevated DHEA-S but normal testosterone. Caused by chronic stress, not insulin.",
                        "3) Inflammatory PCOS — chronic inflammation triggers ovaries to produce excess androgens. Signs: unexplained fatigue, joint pain, skin issues, elevated CRP. Often linked to gut health issues. 4) Post-pill PCOS — temporary hormonal disruption after stopping birth control pills. Usually resolves in 3-6 months. Identifying your type helps target the right treatment approach.",
                    ],
                ],
                [
                    'title' => 'Common PCOS Symptoms',
                    'summary' => 'Recognizing the varied symptoms of PCOS.',
                    'keywords' => 'symptoms,irregular periods,acne,hair loss,hirsutism,weight gain,mood swings',
                    'pages' => [
                        "PCOS symptoms vary widely. Menstrual: irregular cycles (>35 days), missed periods, heavy bleeding. Skin: acne (especially jawline/chin), oily skin, dark patches (acanthosis nigricans) on neck/armpits. Hair: excess facial/body hair (hirsutism), thinning scalp hair. Weight: difficulty losing weight, central obesity.",
                        "Emotional symptoms often overlooked: anxiety, depression, mood swings, irritability, brain fog, fatigue. These are directly linked to hormonal imbalances and insulin resistance. Indian women often attribute these to 'stress' without connecting them to PCOS. HormoneLens tracks these hormonal indicators through your digital twin to show the interconnected impact.",
                    ],
                ],
            ],
            'Hormonal Imbalance in PCOS' => [
                [
                    'title' => 'Androgen Excess',
                    'summary' => 'Understanding elevated male hormones in PCOS women.',
                    'keywords' => 'androgen,testosterone,dheas,male hormone,hirsutism,acne,excess',
                    'pages' => [
                        "In PCOS, ovaries produce excess androgens (male hormones) — primarily testosterone and DHEA-S. Even mildly elevated androgens cause significant symptoms: hirsutism (unwanted facial/body hair), acne, oily skin, and hair thinning. Free testosterone (not just total) should be checked, as it's the biologically active form.",
                        "The insulin-androgen connection: High insulin stimulates ovarian theca cells to produce more testosterone. This is why insulin-resistant PCOS women have higher androgen levels. Reducing insulin through diet and exercise is often more effective than anti-androgen medications. Spearmint tea (2 cups/day) has shown to reduce free testosterone in clinical studies.",
                    ],
                ],
                [
                    'title' => 'LH/FSH Ratio',
                    'summary' => 'The significance of elevated LH relative to FSH in PCOS.',
                    'keywords' => 'lh,fsh,ratio,luteinizing,follicle stimulating,ovulation',
                    'pages' => [
                        "In healthy women, LH and FSH are roughly equal (ratio ~1:1). In PCOS, LH is often 2-3x higher than FSH (ratio >2:1). This imbalanced ratio disrupts normal ovulation. High LH stimulates the ovaries to produce more androgens, while low FSH means follicles don't mature properly, forming cysts.",
                        "An elevated LH/FSH ratio is a diagnostic clue for PCOS but not present in all cases. Blood test should be done on day 2-3 of the menstrual cycle for accuracy. Treatment focuses on restoring hormonal balance through lifestyle changes first. Weight loss of just 5% can improve the LH/FSH ratio significantly.",
                    ],
                ],
                [
                    'title' => 'Thyroid & PCOS Connection',
                    'summary' => 'How thyroid disorders interact with PCOS.',
                    'keywords' => 'thyroid,hypothyroid,tsh,hashimoto,connection,overlap',
                    'pages' => [
                        "Thyroid disorders and PCOS frequently co-occur. Hypothyroidism is found in 25-30% of PCOS patients. Both conditions share symptoms: irregular periods, weight gain, hair issues, fatigue. Undiagnosed hypothyroidism can worsen PCOS symptoms and vice versa. Always check TSH, Free T3, and Free T4 alongside PCOS panels.",
                        "In India, iodine deficiency adds to the thyroid burden. Subclinical hypothyroidism (TSH 4-10) is common and often missed. It worsens insulin resistance and can prevent weight loss despite best efforts. If you have PCOS and can't lose weight despite diet and exercise, get a comprehensive thyroid panel done.",
                    ],
                ],
            ],
            'PCOS & Insulin Resistance' => [
                [
                    'title' => 'Insulin-PCOS Mechanism',
                    'summary' => 'How insulin resistance drives PCOS and creates a vicious cycle.',
                    'keywords' => 'insulin,mechanism,vicious cycle,ovary,androgen production',
                    'pages' => [
                        "70-80% of PCOS women have some degree of insulin resistance, regardless of weight. High insulin → ovaries produce excess androgens → disrupted ovulation → irregular periods and cyst formation. Simultaneously, high insulin → increased fat storage (especially belly) → more insulin resistance → higher androgens. This vicious cycle can only be broken by addressing insulin resistance.",
                        "Lean PCOS: 20-30% of PCOS women are normal weight but still insulin resistant. Their muscles are insulin resistant but ovaries remain sensitive to insulin, getting over-stimulated. Don't ignore PCOS symptoms just because you're thin. Testing: fasting insulin + glucose together (HOMA-IR calculation) is more sensitive than glucose alone.",
                    ],
                ],
                [
                    'title' => 'Weight Management in PCOS',
                    'summary' => 'Why weight loss is harder with PCOS and effective strategies.',
                    'keywords' => 'weight loss,weight management,difficulty,metabolism,pcos weight',
                    'pages' => [
                        "PCOS makes weight loss harder due to: insulin resistance (body stores more fat), higher androgen levels (promote central fat storage), slower metabolism, increased hunger hormones (ghrelin), and potential thyroid issues. A PCOS woman may need to work 2-3x harder than an average woman to lose the same weight.",
                        "Effective strategies: Focus on insulin management, not just calories. Low-GI diet is more effective than low-fat for PCOS weight loss. Strength training builds muscle, which improves insulin sensitivity. Don't do excessive cardio — it raises cortisol, worsening PCOS. Sleep 7-8 hours (sleep deprivation increases weight gain in PCOS). Be patient — aim for 0.5 kg/week, not crash diets.",
                        "Indian approach: Replace white rice with millets (ragi, jowar, bajra — lower GI and higher nutrition). Include protein at every meal (dal, paneer, eggs, sprouts). Cook with coconut oil or ghee in moderate amounts (healthy fats improve satiety). Avoid packaged 'health foods' — they're often high in sugar. Simple home-cooked Indian meals with proper portions are ideal.",
                    ],
                ],
                [
                    'title' => 'PCOS and Diabetes Risk',
                    'summary' => 'Long-term diabetes risk for PCOS women and prevention.',
                    'keywords' => 'diabetes risk,prediabetes,type 2,long term,prevention,screening',
                    'pages' => [
                        "Women with PCOS have a 4-7x higher risk of developing Type-2 diabetes. Up to 40% of PCOS women develop prediabetes or diabetes by age 40. The risk is even higher for Indian women due to genetic predisposition. Annual glucose tolerance testing (not just fasting glucose) is recommended for all PCOS women.",
                        "Prevention is possible: The same lifestyle interventions that manage PCOS prevent diabetes. A combination of low-GI diet + regular exercise + stress management can reduce diabetes conversion by 58% (Diabetes Prevention Program data). Early intervention is key — HormoneLens helps track your metabolic risk trajectory through digital twin simulations.",
                    ],
                ],
            ],
            'Diet & Nutrition for PCOS' => [
                [
                    'title' => 'Anti-Inflammatory PCOS Diet',
                    'summary' => 'Foods that reduce inflammation — a key driver of PCOS.',
                    'keywords' => 'anti-inflammatory,inflammation,omega 3,turmeric,antioxidant,gut health',
                    'pages' => [
                        "Chronic low-grade inflammation worsens PCOS by increasing androgen production. Anti-inflammatory foods: turmeric (haldi) with black pepper, ginger (adrak), green leafy vegetables, berries, fatty fish or flaxseeds (omega-3), nuts especially walnuts and almonds. Cook with cold-pressed oils — coconut, mustard, or olive oil.",
                        "Foods to avoid/reduce: refined sugar, maida (white flour), deep-fried foods, processed meats, excessive dairy (can increase androgens in some women), artificial sweeteners, packaged snacks. Gut health is crucial — 70% of immune system is in the gut. Include fermented foods: homemade curd, kanji, idli/dosa batter (fermented), buttermilk.",
                        "Sample anti-inflammatory Indian day: Morning — warm water with turmeric + lemon. Breakfast — moong dal chilla with mint chutney. Snack — handful of walnuts + green tea. Lunch — millet roti + palak dal + cucumber raita. Evening — roasted makhana + fruit. Dinner — grilled fish/paneer + stir-fried vegetables + small portion brown rice.",
                    ],
                ],
                [
                    'title' => 'PCOS-Friendly Indian Foods',
                    'summary' => 'Specific Indian foods beneficial for managing PCOS symptoms.',
                    'keywords' => 'indian food,pcos friendly,methi,cinnamon,amla,flaxseed,traditional',
                    'pages' => [
                        "Indian kitchen has powerful PCOS remedies: Methi (fenugreek) — soaked seeds improve insulin sensitivity and reduce testosterone. Dalchini (cinnamon) — 1/2 tsp daily regulates menstrual cycles. Flaxseed (alsi) — 1 tbsp ground daily reduces androgens. Amla — high vitamin C, powerful antioxidant, supports liver detoxification of hormones.",
                        "More PCOS superfoods: Kalonji (black seed) — studies show it reduces weight and improves lipid profile in PCOS. Moringa (drumstick leaves) — rich in iron, supports adrenal function. Tulsi — adaptogen that reduces cortisol. Jeera (cumin) water — aids digestion and reduces bloating. Triphala — Ayurvedic formula for gut health and detoxification.",
                    ],
                ],
                [
                    'title' => 'Supplements for PCOS',
                    'summary' => 'Evidence-based supplements that support PCOS management.',
                    'keywords' => 'supplements,inositol,vitamin d,zinc,magnesium,omega 3,berberine',
                    'pages' => [
                        "Evidence-based PCOS supplements: Myo-inositol (4g/day) — improves ovulation and insulin sensitivity, comparable to metformin in studies. Vitamin D (most Indian PCOS women are deficient) — 60,000 IU weekly if levels <20 ng/mL. Omega-3 (2g/day) — reduces inflammation and testosterone. Zinc (25mg/day) — reduces hirsutism and hair loss.",
                        "Additional helpful supplements: Magnesium (200-400mg/day) — reduces insulin resistance, improves sleep, reduces anxiety. Chromium (200mcg/day) — improves glucose tolerance. Berberine (500mg 2-3x/day) — similar to metformin for insulin resistance. NAC (N-acetyl cysteine, 600mg 2x/day) — antioxidant that improves ovulation. Always consult doctor before starting supplements.",
                    ],
                ],
            ],
            'Lifestyle & Mental Health' => [
                [
                    'title' => 'Exercise for PCOS',
                    'summary' => 'Best exercise types and routines for PCOS management.',
                    'keywords' => 'exercise,workout,strength training,yoga,walking,hiit,pcos exercise',
                    'pages' => [
                        "Best exercises for PCOS (in order of impact): 1) Strength/resistance training — builds muscle, improves insulin sensitivity, boosts metabolism. 2-3 sessions/week. 2) Walking — 30 min daily, preferably post-meal. 3) Yoga — reduces cortisol, improves hormonal balance. 4) Low-impact HIIT — short bursts improve cardio fitness without excessive cortisol.",
                        "Exercises to be cautious with: Prolonged intense cardio (>60 min running, cycling) can raise cortisol and worsen PCOS. Over-exercising worsens stress and can cause missed periods. The sweet spot: 4-5 days/week, 30-45 min sessions, mix of strength + moderate cardio + yoga/stretching. Movement throughout the day matters more than one intense gym session.",
                    ],
                ],
                [
                    'title' => 'Sleep & PCOS',
                    'summary' => 'How sleep quality affects PCOS and strategies for better sleep.',
                    'keywords' => 'sleep,insomnia,sleep quality,circadian,melatonin,sleep hygiene',
                    'pages' => [
                        "PCOS women are 2x more likely to have sleep disorders. Poor sleep worsens insulin resistance by 25-30%, increases cortisol and hunger hormones, and disrupts reproductive hormones. Sleep apnea is common in PCOS (even in non-overweight women) — if you snore or wake tired, get evaluated.",
                        "Sleep hygiene for PCOS: Fixed sleep/wake time (even weekends). Dark room — melatonin is crucial for ovarian function. No screens 1 hour before bed. Evening routine: warm milk with turmeric (golden milk), journaling, gentle stretching. Magnesium supplement at bedtime helps both sleep and insulin sensitivity. Aim for 7-8 hours — this alone can improve hormonal markers.",
                    ],
                ],
                [
                    'title' => 'Stress & Mental Health',
                    'summary' => 'Managing the psychological impact of PCOS.',
                    'keywords' => 'stress,anxiety,depression,mental health,self care,cortisol,mindfulness',
                    'pages' => [
                        "PCOS significantly impacts mental health: 40% of PCOS women experience anxiety, 30% experience depression. Body image issues from acne, hirsutism, weight gain, and hair loss compound the emotional burden. Hormonal fluctuations directly affect mood and cognitive function. This is NOT 'just in your head' — it's a biochemical reality.",
                        "Management strategies: Acknowledge that PCOS is a medical condition, not a personal failing. Practice mindfulness/meditation (even 10 min/day reduces cortisol by 20%). Build a support system — online PCOS communities can help. Journaling reduces anxiety. If symptoms are severe, cognitive behavioral therapy (CBT) is effective. Don't hesitate to seek professional help.",
                        "Indian context: Stigma around menstrual issues and infertility adds pressure. Family expectations can increase stress. Educate family members about PCOS. Remember: PCOS is manageable, and fertility is possible with proper management. HormoneLens helps you see how lifestyle changes directly improve your hormonal twin scores, providing motivation and evidence of progress.",
                    ],
                ],
            ],
        ];
    }
}
