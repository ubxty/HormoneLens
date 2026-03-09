<?php

namespace Database\Seeders;

use App\Models\RagDocument;
use App\Models\RagNode;
use App\Models\RagPage;
use Illuminate\Database\Seeder;

class ThyroidRagSeeder extends Seeder
{
    public function run(): void
    {
        $doc = RagDocument::updateOrCreate(
            ['title' => 'Thyroid Disorders Knowledge Base'],
            ['description' => 'Comprehensive reference for thyroid conditions, symptoms, lifestyle management, and hormonal interactions.']
        );

        // Clear existing nodes for this document (idempotent)
        $doc->nodes()->delete();

        // ── Root node: Thyroid Overview ──
        $root = $this->node($doc, null, 'Thyroid Disorders', 0,
            'thyroid,disorders,hypothyroid,hyperthyroid,tsh,t4,hormones,metabolism',
            'Overview of thyroid disorders including hypothyroidism, hyperthyroidism, Hashimoto\'s, and Graves\' disease.'
        );

        // ── Branch 1: Types of Thyroid Conditions ──
        $types = $this->node($doc, $root, 'Types of Thyroid Conditions', 1,
            'hypothyroid,hyperthyroid,hashimotos,graves,thyroiditis,autoimmune',
            'Classification of thyroid disorders: hypothyroidism, hyperthyroidism, Hashimoto\'s thyroiditis, Graves\' disease.'
        );

        $this->page($types, 1, <<<'TEXT'
## Hypothyroidism (Underactive Thyroid)
Hypothyroidism occurs when the thyroid gland doesn't produce enough thyroid hormones. This slows metabolism and affects nearly every organ.

**Key Indicators:**
- TSH > 4.5 mIU/L (elevated)
- Free T4 < 0.8 ng/dL (low)

**Common Symptoms:** Fatigue, weight gain, cold intolerance, dry skin, constipation, depression, brain fog, slow heart rate, hair thinning.

**Impact on Metabolism:** Reduced basal metabolic rate by 10-40%. Increased insulin resistance. Higher cortisol sensitivity. Tendency toward higher cholesterol.

**Hormonal Interactions:**
- Elevated TSH stimulates thyroid but production remains low
- Low T3/T4 reduces glucose metabolism efficiency
- Cortisol-thyroid axis: chronic stress worsens hypothyroidism
- Sleep disruption common → further elevates cortisol
TEXT
        );

        $this->page($types, 2, <<<'TEXT'
## Hyperthyroidism (Overactive Thyroid)
Hyperthyroidism occurs when the thyroid produces too much thyroid hormone, accelerating metabolism.

**Key Indicators:**
- TSH < 0.4 mIU/L (suppressed)
- Free T4 > 1.8 ng/dL (elevated)

**Common Symptoms:** Rapid heartbeat, weight loss, anxiety, tremors, heat intolerance, increased appetite, diarrhea, insomnia, irritability.

**Impact on Metabolism:** Increased basal metabolic rate. Accelerated glucose absorption. Higher insulin demand. Bone density loss risk.

## Hashimoto's Thyroiditis
Most common cause of hypothyroidism. Autoimmune condition where immune system attacks thyroid tissue.
- Fluctuating TSH levels (can swing between hypo and hyper)
- Often triggered or worsened by stress, gluten sensitivity, selenium deficiency

## Graves' Disease
Most common cause of hyperthyroidism. Autoimmune condition producing thyroid-stimulating antibodies.
- Can cause eye problems (Graves' ophthalmopathy)
- Increased cardiovascular risk
TEXT
        );

        // ── Branch 2: Thyroid and Lifestyle ──
        $lifestyle = $this->node($doc, $root, 'Thyroid and Lifestyle Management', 1,
            'diet,exercise,sleep,stress,nutrition,selenium,iodine,lifestyle,thyroid',
            'How diet, exercise, sleep, and stress management affect thyroid function.'
        );

        $this->page($lifestyle, 1, <<<'TEXT'
## Nutrition for Thyroid Health

**Beneficial Nutrients:**
- **Selenium:** Essential for T4→T3 conversion. Sources: Brazil nuts, fish, eggs, sunflower seeds.
- **Iodine:** Building block for thyroid hormones. Sources: seaweed, dairy, iodized salt. Caution: excess iodine can worsen Hashimoto's.
- **Zinc:** Supports thyroid hormone synthesis. Sources: pumpkin seeds, lentils, chickpeas.
- **Vitamin D:** Low levels correlated with autoimmune thyroid disease. Sources: sunlight, fatty fish, fortified foods.
- **Iron:** Required for thyroid peroxidase enzyme. Deficiency impairs thyroid function.

**Foods to Limit:**
- **Goitrogens** (raw cruciferous vegetables in excess): broccoli, kale, cauliflower — cooking reduces goitrogenic effect
- **Soy products:** may interfere with thyroid hormone absorption
- **Highly processed foods:** increase inflammation, worsen autoimmune conditions
- **Excess sugar:** increases cortisol, destabilizes thyroid axis

**Meal Timing:** Regular meals help maintain stable blood sugar, which supports thyroid function. Skipping meals raises cortisol.
TEXT
        );

        $this->page($lifestyle, 2, <<<'TEXT'
## Exercise and Thyroid Function

**Hypothyroidism:**
- Low-to-moderate intensity exercise recommended initially (walking, yoga, swimming)
- Gradually increase intensity as thyroid levels stabilize
- High-intensity exercise when severely hypothyroid increases cortisol and worsens symptoms
- Aim for 150 minutes/week of moderate activity

**Hyperthyroidism:**
- Avoid high-intensity until thyroid levels normalize (increased cardiovascular risk)
- Focus on calming exercises: yoga, tai chi, gentle walking
- Weight-bearing exercises important for bone density protection

## Sleep and Thyroid
- Poor sleep directly worsens thyroid function: increases TSH, reduces T3 conversion
- Hypothyroid patients often experience sleep apnea → further metabolic disruption
- Target 7-9 hours; maintain consistent sleep schedule
- Blue light exposure before bed disrupts melatonin → thyroid axis

## Stress Management
- Chronic stress → elevated cortisol → suppressed TSH → reduced thyroid function
- Stress is the #1 modifiable factor for thyroid autoimmunity flares
- Recommended: meditation, deep breathing, nature exposure, social connection
TEXT
        );

        // ── Branch 3: Thyroid and Other Conditions ──
        $interactions = $this->node($doc, $root, 'Thyroid Interactions with Other Conditions', 1,
            'pcos,diabetes,insulin,cortisol,fertility,pregnancy,thyroid,interaction',
            'How thyroid disorders interact with PCOS, diabetes, fertility, and cortisol.'
        );

        $this->page($interactions, 1, <<<'TEXT'
## Thyroid and PCOS Connection
- 22-27% of PCOS patients have thyroid dysfunction
- Both conditions share insulin resistance as a common pathway
- Hypothyroidism can mimic PCOS symptoms: weight gain, irregular periods, hair loss
- TSH > 2.5 may affect fertility even within "normal" range
- Treatment of hypothyroidism often improves PCOS symptoms

## Thyroid and Diabetes
- Hypothyroidism increases insulin resistance → harder to control blood sugar
- Type 1 diabetes patients have 3x higher risk of autoimmune thyroid disease
- Metformin (common diabetes medication) may lower TSH
- Both conditions benefit from anti-inflammatory diet and regular exercise

## Thyroid and Cortisol
- Cortisol and thyroid hormones have bidirectional relationship
- High cortisol → reduced T4 to T3 conversion (conversion happens in liver)
- Chronic stress → thyroid hormone resistance at cellular level
- Night shift workers have 2x higher thyroid disorder risk due to cortisol disruption

## Thyroid and Fertility
- TSH > 2.5 mIU/L associated with reduced fertility
- Hypothyroidism increases miscarriage risk
- Proper thyroid management essential before and during pregnancy
- TSH target during pregnancy: 0.1-2.5 mIU/L (first trimester)
TEXT
        );

        // ── Branch 4: Monitoring and Simulation ──
        $monitoring = $this->node($doc, $root, 'Thyroid Monitoring and Risk Assessment', 1,
            'tsh,t4,t3,monitoring,risk,assessment,blood,test,levels,thyroid',
            'Understanding thyroid lab values, risk indicators, and simulation parameters.'
        );

        $this->page($monitoring, 1, <<<'TEXT'
## Understanding Thyroid Lab Values

| Test | Normal Range | Hypothyroid | Hyperthyroid |
|------|-------------|-------------|--------------|
| TSH | 0.4-4.0 mIU/L | > 4.5 (elevated) | < 0.4 (suppressed) |
| Free T4 | 0.8-1.8 ng/dL | < 0.8 (low) | > 1.8 (high) |
| Free T3 | 2.3-4.2 pg/mL | < 2.3 (low) | > 4.2 (high) |
| TPO Antibodies | < 35 IU/mL | Often elevated (Hashimoto's) | Normal or elevated |

## Risk Factors for Thyroid Dysfunction
1. **Female sex** — 5-8x more likely than males
2. **Age > 35** — increasing prevalence
3. **Family history** — strong genetic component
4. **Other autoimmune conditions** — celiac, type 1 diabetes, rheumatoid arthritis
5. **High stress lifestyle** — cortisol-mediated thyroid suppression
6. **Poor sleep** — < 6 hours consistently
7. **Iodine extremes** — both deficiency and excess

## Simulation Parameters
When simulating thyroid impact in HormoneLens:
- TSH changes of ±1 mIU/L shift metabolic risk by ~5-10 points
- Sleep improvement from 5→7 hours can improve thyroid markers by 10-15%
- Stress reduction from 'high' to 'low' can stabilize TSH fluctuations
- Regular exercise at moderate intensity improves T4→T3 conversion efficiency
TEXT
        );
    }

    private function node(RagDocument $doc, ?RagNode $parent, string $title, int $depth, string $keywords, string $summary): RagNode
    {
        return RagNode::create([
            'document_id' => $doc->id,
            'parent_id' => $parent?->id,
            'title' => $title,
            'depth' => $depth,
            'keywords' => $keywords,
            'summary' => $summary,
        ]);
    }

    private function page(RagNode $node, int $pageNumber, string $content): RagPage
    {
        return RagPage::create([
            'node_id' => $node->id,
            'page_number' => $pageNumber,
            'content' => $content,
        ]);
    }
}
