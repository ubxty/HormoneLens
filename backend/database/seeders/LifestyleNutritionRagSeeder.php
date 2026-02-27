<?php

namespace Database\Seeders;

use App\Models\RagDocument;
use App\Models\RagNode;
use App\Models\RagPage;
use Illuminate\Database\Seeder;

class LifestyleNutritionRagSeeder extends Seeder
{
    public function run(): void
    {
        $doc = RagDocument::create([
            'title' => 'Lifestyle & Nutrition Knowledge Base',
            'description' => 'General lifestyle, sleep, stress, and Indian nutrition guidance applicable to both diabetes and PCOS management.',
        ]);

        $roots = [
            [
                'title' => 'Sleep & Recovery',
                'summary' => 'Impact of sleep on metabolic health, hormones, and practical improvement strategies.',
                'keywords' => 'sleep,insomnia,circadian,rest,recovery,sleep quality,hours,melatonin',
            ],
            [
                'title' => 'Stress Management',
                'summary' => 'How chronic stress affects hormones and metabolism, with evidence-based coping techniques.',
                'keywords' => 'stress,cortisol,anxiety,relaxation,meditation,breathing,chronic stress',
            ],
            [
                'title' => 'Indian Nutrition Guide',
                'summary' => 'Comprehensive guide to Indian foods, their glycemic impact, and healthy alternatives.',
                'keywords' => 'indian food,nutrition,glycemic,healthy eating,traditional,thali,balanced diet',
            ],
            [
                'title' => 'Hydration & Detox',
                'summary' => 'Water intake, traditional Indian drinks, and their metabolic benefits.',
                'keywords' => 'water,hydration,dehydration,detox,drinks,buttermilk,green tea,herbal',
            ],
            [
                'title' => 'Gut Health & Metabolism',
                'summary' => 'The gut-hormone connection and how gut health affects metabolic conditions.',
                'keywords' => 'gut health,microbiome,probiotics,digestion,fermented,fiber,prebiotics',
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
            'Sleep & Recovery' => [
                [
                    'title' => 'Sleep Duration & Quality',
                    'summary' => 'How much sleep you need and what constitutes quality sleep.',
                    'keywords' => 'duration,hours,quality,deep sleep,rem,stages',
                    'pages' => [
                        "Adults need 7-9 hours of sleep. For metabolic health, 7-8 hours is optimal. Less than 6 hours increases diabetes risk by 28% and worsens insulin resistance within just 4 days. Quality matters as much as quantity — deep sleep (stages 3-4) is when growth hormone peaks and insulin sensitivity resets.",
                        "Signs of poor sleep quality: waking unrefreshed, daytime fatigue, needing caffeine to function, irritability, difficulty concentrating. Indian lifestyle factors that harm sleep: late dinners, evening chai/coffee, screen time with blue light, irregular schedules. Even one week of 6-hour sleep raises inflammatory markers significantly.",
                    ],
                ],
                [
                    'title' => 'Sleep Hygiene Practices',
                    'summary' => 'Practical tips for better sleep in the Indian lifestyle context.',
                    'keywords' => 'sleep hygiene,routine,bedtime,tips,improve sleep,habits',
                    'pages' => [
                        "Sleep hygiene essentials: 1) Fixed sleep-wake schedule (±30 min, even weekends). 2) Cool, dark room — 20-22°C is ideal. 3) No screens 60 min before bed. 4) Last meal 2-3 hours before sleep. 5) No caffeine after 2 PM — this includes chai. 6) A calming routine: warm milk with nutmeg (jaiphal), light reading, or pranayama.",
                        "Indian evening routine for better sleep: Light dinner by 7:30 PM — khichdi, soup, or roti with subzi. Golden milk (haldi doodh) 30 min before bed. 10 minutes of Shavasana or Yoga Nidra. Keep your phone in another room. If racing thoughts prevent sleep, try 'brain dump' journaling — write down everything on your mind for 5 minutes.",
                    ],
                ],
                [
                    'title' => 'Sleep & Hormones',
                    'summary' => 'The bidirectional relationship between sleep and hormonal health.',
                    'keywords' => 'hormones,melatonin,cortisol,growth hormone,insulin,sleep hormone',
                    'pages' => [
                        "Sleep regulates key hormones: Melatonin (sleep signal) — also a powerful antioxidant for ovaries. Cortisol — should be lowest at midnight, highest at 7 AM. Growth hormone — 75% is released during deep sleep. Leptin and ghrelin (hunger hormones) — poor sleep increases ghrelin (hunger) and decreases leptin (fullness), explaining cravings after poor sleep.",
                        "For PCOS women: Melatonin directly supports egg quality and ovarian function. For diabetics: One night of poor sleep can make you as insulin resistant as if you gained 10 kg. Cortisol disruption from irregular sleep worsens both conditions. Prioritize sleep as a medical intervention, not a luxury.",
                    ],
                ],
            ],
            'Stress Management' => [
                [
                    'title' => 'Cortisol & Metabolic Impact',
                    'summary' => 'How chronic stress through cortisol damages metabolic health.',
                    'keywords' => 'cortisol,chronic stress,metabolic,belly fat,blood sugar,inflammation',
                    'pages' => [
                        "Cortisol is the 'stress hormone.' Acute stress raises cortisol temporarily — that's normal. Chronic stress keeps cortisol elevated, causing: increased blood sugar (cortisol tells liver to release glucose), belly fat accumulation, insulin resistance, inflammation, disrupted ovulation (PCOS), poor sleep, increased appetite for high-sugar foods.",
                        "Indian stressors to be mindful of: work-life imbalance, family expectations, financial pressures, commute stress, noise pollution, social comparison. Chronic low-level stress (constant worry) is more damaging than occasional acute stress. Your body can't distinguish between a tiger attack and work deadline stress — it produces the same cortisol.",
                    ],
                ],
                [
                    'title' => 'Breathing & Meditation',
                    'summary' => 'Simple breathwork and meditation techniques for daily stress management.',
                    'keywords' => 'breathing,meditation,pranayama,mindfulness,box breathing,calm',
                    'pages' => [
                        "Breathing techniques activate the vagus nerve, instantly reducing cortisol. Box Breathing: Inhale 4 counts → Hold 4 → Exhale 4 → Hold 4. Repeat 4-8 cycles. Takes 2-3 minutes and can be done anywhere — at your desk, in traffic, before meals. 4-7-8 Breathing: Inhale 4 → Hold 7 → Exhale 8. Especially effective for sleep.",
                        "Pranayama for metabolic health: Anulom Vilom (alternate nostril) — balances left-right brain, reduces cortisol by 20%. Bhramari (bee breath) — calms nervous system, reduces anxiety. Start with 5 minutes/day, build to 15. Meditation: Start with guided apps, just 10 min/day creates measurable brain changes within 8 weeks. Meditation reduces HbA1c and improves PCOS hormones in clinical trials.",
                    ],
                ],
                [
                    'title' => 'Adaptogenic Herbs',
                    'summary' => 'Indian herbs that help the body adapt to stress.',
                    'keywords' => 'adaptogens,ashwagandha,tulsi,brahmi,shatavari,herbs,ayurveda',
                    'pages' => [
                        "Adaptogens are herbs that help the body resist stress. Indian adaptogens: Ashwagandha — reduces cortisol by 30%, improves thyroid function, reduces anxiety. Dose: 300-600mg standardized extract/day. Tulsi (Holy Basil) — reduces blood sugar and cortisol. 2-3 cups of tulsi tea/day. Brahmi — improves cognitive function, reduces anxiety.",
                        "Shatavari — especially beneficial for women's hormonal health, balances estrogen. Amla — adaptogenic berry rich in vitamin C, supports adrenal function. These are traditionally used in Ayurveda and now backed by modern research. Always choose standardized extracts and consult a healthcare provider, especially if on medications.",
                    ],
                ],
            ],
            'Indian Nutrition Guide' => [
                [
                    'title' => 'Traditional Indian Superfoods',
                    'summary' => 'Nutrient-dense Indian foods with proven health benefits.',
                    'keywords' => 'superfoods,moringa,turmeric,amla,ghee,millets,traditional',
                    'pages' => [
                        "India's traditional foods are nutritional powerhouses: Turmeric — curcumin is anti-inflammatory, improves insulin sensitivity. Moringa (drumstick) — complete amino acid profile, iron-rich. Millets (ragi/jowar/bajra) — lower GI than wheat/rice, higher mineral content. Ghee (clarified butter) — in moderation, contains butyrate for gut health and fat-soluble vitamin absorption.",
                        "More superfoods: Jackfruit (kathal) — raw jackfruit has very low GI, excellent rice substitute. Bottle gourd (lauki) — alkaline, aids weight loss, improves digestion. Bitter gourd (karela) — contains plant-insulin (polypeptide-p). Curry leaves (kadi patta) — improve lipid profile and glucose metabolism. Fenugreek (methi) seeds — high in soluble fibre, lower blood sugar. These are more effective and affordable than imported 'superfoods'.",
                    ],
                ],
                [
                    'title' => 'Healthy Cooking Methods',
                    'summary' => 'How cooking methods affect nutritional value and glycemic impact.',
                    'keywords' => 'cooking,method,oil,deep fry,steam,grill,pressure cook,tadka',
                    'pages' => [
                        "Cooking methods significantly affect food's health impact: Best: steaming, grilling, sautéing with minimal oil, pressure cooking. Moderate: shallow frying, tadka (tempering). Avoid: deep frying, reheating oil. Rice trick: Cook rice with 1 tsp coconut oil, cool completely, then reheat — this increases resistant starch by 60%, effectively lowering the GI.",
                        "Oil guide for Indian cooking: Mustard oil — high smoke point, pungent, anti-bacterial. Coconut oil — best for South Indian cooking, contains MCTs. Groundnut oil — good for high-heat cooking. Ghee — excellent for tadka, don't fear moderate use. Olive oil — use for salads and low-heat cooking only. Avoid: refined sunflower/soybean oil for high-heat cooking (oxidizes easily). Use a variety of oils rather than sticking to one.",
                    ],
                ],
                [
                    'title' => 'Regional Diet Adaptations',
                    'summary' => 'Healthy adaptations of different regional Indian cuisines.',
                    'keywords' => 'north indian,south indian,regional,gujarati,bengali,punjabi',
                    'pages' => [
                        "North Indian: Replace naan with tandoori roti or multigrain roti. Use less cream/butter in gravies — try cashew paste or onion-tomato base instead. Dal makhani is nutritious but reduce butter; rajma and chole are excellent protein sources. Reduce punjabi-style deep-fried snacks (samosa, pakora) to occasional treats.",
                        "South Indian: Idli and dosa batter is fermented (good for gut!) but combine with sambar (protein) not just chutney. Replace white rice with red/brown rice or millets. Rasam is excellent — anti-inflammatory spices. Limit coconut-heavy dishes if watching calories. Bengali: Fish is great (omega-3!), but reduce deep-fried fish preparations. Use mustard instead of salt for flavoring. Gujarati: Moderate the sweetness in traditional dishes, increase vegetable portions.",
                    ],
                ],
            ],
            'Hydration & Detox' => [
                [
                    'title' => 'Water & Metabolic Health',
                    'summary' => 'How hydration affects blood sugar, metabolism, and hormones.',
                    'keywords' => 'water,hydration,dehydration,metabolism,thirst,water intake',
                    'pages' => [
                        "Even mild dehydration (1-2%) impairs metabolism and raises blood sugar. Dehydration triggers the liver to release more glucose. Aim for 2.5-3L water daily (more in Indian summer). Signs of dehydration beyond thirst: dark urine, headaches, fatigue, difficulty concentrating. Drinking 500ml water before meals reduces calorie intake and post-meal blood sugar.",
                        "Timing matters: Glass of warm water on waking jump-starts metabolism. Sipping throughout the day is better than gulping large amounts. Don't replace water with chai/coffee (caffeine is a diuretic). Avoid ice-cold water during meals — it can slow digestion. Room temperature or warm water is better for metabolism.",
                    ],
                ],
                [
                    'title' => 'Traditional Indian Drinks',
                    'summary' => 'Health benefits of traditional Indian beverages.',
                    'keywords' => 'buttermilk,chaas,nimbu pani,green tea,kadha,jaljeera,drinks',
                    'pages' => [
                        "Chaas (buttermilk) — probiotic, cools the body, aids digestion, low calorie. Add cumin and coriander. Nimbu Pani — vitamin C, electrolytes, alkalizing. Use minimal sugar; try with rock salt and roasted cumin. Kadha (herbal decoction) — immune boosting, with tulsi, ginger, pepper, cinnamon. Excellent for inflammation.",
                        "Green tea — catechins improve insulin sensitivity and aid weight loss. 2-3 cups/day without sugar. Jeera water — boosts metabolism, reduces bloating. Soak 1 tsp cumin in warm water overnight, drink in morning. Ajwain (carom) water — excellent for digestion and reducing belly bloating. Avoid: packaged fruit juices (pure sugar), sugary lassi, excessive chai with sugar.",
                    ],
                ],
            ],
            'Gut Health & Metabolism' => [
                [
                    'title' => 'Gut-Hormone Connection',
                    'summary' => 'How gut microbiome affects hormones and metabolic health.',
                    'keywords' => 'gut,microbiome,hormone,estrobolome,inflammation,leaky gut',
                    'pages' => [
                        "The gut houses 70% of the immune system and produces hormones that affect blood sugar (GLP-1, GIP), appetite, and mood (95% of serotonin is made in the gut). The 'estrobolome' — a collection of gut bacteria — metabolizes estrogen. Imbalanced gut bacteria can recirculate estrogen, worsening PCOS. Gut inflammation triggers systemic inflammation, aggravating both diabetes and PCOS.",
                        "Leaky gut (intestinal permeability) is common in both PCOS and diabetes. Undigested food particles enter the bloodstream, triggering immune responses and inflammation. Causes: processed food, excessive sugar, antibiotics, stress, NSAIDs. Healing requires removing irritants and rebuilding the gut lining with L-glutamine, bone broth, and fermented foods.",
                    ],
                ],
                [
                    'title' => 'Probiotics & Fermented Foods',
                    'summary' => 'Indian fermented foods and their impact on metabolic health.',
                    'keywords' => 'probiotics,fermented,curd,dahi,idli,dosa,kanji,pickle',
                    'pages' => [
                        "Indian cuisine is naturally rich in probiotics: Dahi (curd) — the most accessible probiotic. Have 1 katori daily, preferably homemade (higher bacterial diversity than packaged). Fermented idli/dosa batter — the fermentation increases B-vitamins and reduces antinutrients. Kanji (fermented carrot drink) — probiotic + prebiotic. Traditional pickles (not vinegar-based) — lacto-fermented, full of beneficial bacteria.",
                        "Prebiotics (food for good bacteria): Garlic, onion (allium family) — rich in FOS. Banana (especially raw) — resistant starch. Whole grains and legumes. Flaxseeds. Guidelines: Start slowly — sudden increase in fermented foods can cause bloating. Combination of probiotics + prebiotics (synbiotics) is most effective. Avoid probiotic supplements with added sugar.",
                    ],
                ],
                [
                    'title' => 'Fiber & Digestive Health',
                    'summary' => 'Role of dietary fiber in blood sugar control and gut health.',
                    'keywords' => 'fiber,fibre,soluble,insoluble,psyllium,isabgol,whole grain,roughage',
                    'pages' => [
                        "Fiber is crucial for metabolic health: Soluble fiber (dal, oats, isabgol/psyllium) — forms a gel that slows sugar absorption, reducing post-meal spikes by 20-30%. Insoluble fiber (vegetables, whole grains, bran) — feeds gut bacteria and promotes regular bowel movements. Aim for 25-30g/day; most Indians consume only 15g.",
                        "Easy ways to increase fiber: Add 1 tsp isabgol (psyllium husk) to water before meals — clinically proven to lower blood sugar and cholesterol. Eat whole fruits instead of juice. Choose whole dal over washed dal. Don't peel cucumbers, apples, or pears. Add flaxseed powder to atta (flour) when making rotis. Include a salad or raita with every meal.",
                    ],
                ],
            ],
        ];
    }
}
