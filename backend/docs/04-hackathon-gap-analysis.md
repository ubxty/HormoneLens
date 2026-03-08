# HormoneLens — Hackathon Gap Analysis & Feature Roadmap

## Source: `initial_submission.pdf` (Hackathon Proposal) vs Current Codebase (March 2026)

---

## PART 1: GAP ANALYSIS — What Was Promised vs What Exists

### LEGEND
- **DONE** = Fully implemented and functional
- **PARTIAL** = Structure exists but missing core capability
- **MISSING** = Not implemented at all
- **BONUS** = Implemented but wasn't in original proposal

---

### 1. CORE FEATURES (from "List of features offered by the solution")

| # | Promised Feature | Status | What Exists | What's Missing |
|---|-----------------|--------|-------------|----------------|
| 1 | **Pre-Meal "Flash Forward" Simulation** — Predict metabolic cost of a meal before eating, accounting for current stress/sleep | **PARTIAL** | `FoodImpactController` + `SimulationService.simulateFoodImpact()` — classifies food as high/low GI, adjusts `avg_blood_sugar` by fixed values (+40/-15 mg/dL), suggests alternatives | No time-dependent glucose curve (peak, duration, recovery). No cross-interaction with current stress/sleep state during food simulation. No individual insulin response variation. Binary GI classification, not predictive. |
| 2 | **Context-Aware RAG Engine** — Retrieves user's specific historical data ("You spike on rice, but not on wheat") | **PARTIAL** | `RagSearchService` with keyword tree traversal, 3 knowledge base documents (Diabetes, PCOD, Lifestyle), disease_context filter | RAG is **entirely static** — searches pre-seeded documents only. Does NOT retrieve any user history. Cannot say "You spike on rice" because it has no user-specific memory. Only accepts `disease_context` ("diabetes"/"pcod") as personalization. |
| 3 | **Dynamic Metabolic Risk Score** — Real-time multivariate score that updates instantly | **PARTIAL** | 5-component risk score (metabolic, insulin, sleep, stress, diet) + overall weighted score + risk categories (LOW→CRITICAL) | Score only updates on explicit API calls (`POST /digital-twin/generate` or `POST /simulations/run`). No real-time updates. No WebSocket/Reverb. No live dashboard meter. |
| 4 | **Explainable AI Insights** — Natural language explanations referencing user context (e.g., "Avoid pizza tonight because your sleep was poor (5h)") | **PARTIAL** | RAG returns `reasoning_path` (array of node titles traversed) and `answer` (extracted from knowledge base pages). AlertService generates templated alert messages. | Explanations are pre-written KB text extracts, NOT dynamically generated. No LLM synthesis. Cannot compose user-specific sentences like "your sleep was 5h, reducing insulin sensitivity". Alerts use hardcoded templates. |
| 5 | **Clinical Safety Guardrails** — Amazon Bedrock Guardrails to filter harmful advice and prevent hallucinated prescriptions | **MISSING** | No safety filtering layer exists. No content moderation. No Bedrock Guardrails. | Entire guardrails layer absent. No response filtering. No disclaimer generation. No medical advice boundary enforcement. |

---

### 2. ARCHITECTURE COMPONENTS (from "System Architecture")

| # | Promised Component | Status | What Exists | What's Missing |
|---|-------------------|--------|-------------|----------------|
| 6 | **Amazon Bedrock (Reasoning Engine)** — Complex semantic reasoning across sleep, stress, nutrition | **MISSING** | Risk calculation uses hardcoded weighted formulas (e.g., `overall = 0.4×metabolic + 0.3×insulin + 0.3×hormonal`). No LLM involvement. | No AWS SDK. No Bedrock API calls. No Claude model invocations. No semantic reasoning. Entire AI core is rule-based arithmetic. |
| 7 | **ChromaDB (Vector Database)** — Retrieves user's unique historical health patterns | **MISSING** | No vector DB. No embeddings. No similarity search. RAG uses SQL keyword matching on `rag_nodes.keywords` column. | Zero vector infrastructure. No embedding generation. No semantic similarity. |
| 8 | **Redis (Cache Layer)** — Caches frequent simulation requests | **PARTIAL** | Redis config exists in `config/cache.php` and `config/database.php`. | Not active — `CACHE_STORE=database` in .env.example. No simulation result caching implemented. |
| 9 | **Laravel Reverb (Real-time Engine)** — Pushes live risk alerts to dashboard | **MISSING** | `BROADCAST_CONNECTION=log` (no-op). No Reverb package. No Event broadcasting classes. No WebSocket channels. | Zero real-time capability. Alerts are only visible on page refresh or API poll. |
| 10 | **Chart.js (Data Visualization)** — Renders predicted glucose spike graphs | **MISSING** | No Chart.js in package.json. Frontend has Three.js (3D character), React, Framer Motion. Dashboard blade template exists but no charting library. | No glucose spike charts. No risk trend graphs. No simulation comparison visualizations. |
| 11 | **PostgreSQL** — Structured user profiles & logs | **PARTIAL** | Config supports PostgreSQL but defaults to **SQLite** (`DB_CONNECTION=sqlite`). | Not using PostgreSQL by default as proposed. Easy fix but needs migration. |
| 12 | **Claude 3.5 Sonnet (Response Synthesis)** — Translates biological risks into natural language insights | **MISSING** | No Claude API integration. All text is pre-written in RAG page content or hardcoded alert templates. | No LLM-based text generation anywhere. |

---

### 3. SIMULATION PREDICTIONS (from "Simulation Output" table)

| # | Promised Prediction | Disease | Status | What's Missing |
|---|-------------------|---------|--------|----------------|
| 13 | **Insulin Sensitivity prediction** | Both | **PARTIAL** | `insulin_resistance_score` exists (0-100) but calculated by static formula, not predictive model |
| 14 | **Predict Androgen Imbalance** | PCOS | **PARTIAL** | PCOD fields track symptoms (hirsutism, acne, hair_thinning) and calculate `hormonal_imbalance` score, but no direct androgen level prediction |
| 15 | **Predict Glucose Spike** | Diabetes | **PARTIAL** | Food impact adjusts `avg_blood_sugar` by fixed amounts. No time-series spike prediction, no peak timing, no individual response curves |
| 16 | **Sleep Cycle Impact** | Both | **DONE** | `calculateSleepScore()` maps hours to 0-100. Sleep simulation modifies `avg_sleep_hours` and recalculates overall risk |
| 17 | **Cycle Delay Risk Forecast** | PCOS | **PARTIAL** | `cycle_regularity` field exists (regular/irregular/missed) but no predictive forecasting of cycle delays based on lifestyle changes |
| 18 | **Fasting Sugar Variation** | Diabetes | **PARTIAL** | `avg_blood_sugar` field tracked but no fasting vs fed distinction. No time-of-day variation modeling |
| 19 | **Meal Timing Effect** | Both | **MISSING** | No meal timing data. No time-based food impact. `food_item` is the only input — no "when" |
| 20 | **Ovulation Stability Risk** | PCOS | **PARTIAL** | Proxied by `cycle_regularity` field. No direct ovulation tracking or stability scoring |
| 21 | **Post-meal Sugar Level** | Diabetes | **PARTIAL** | Food impact modifies `avg_blood_sugar` but no postprandial-specific measurement. No time curve |
| 22 | **Cortisol Imbalance Detection** | Both | **MISSING** | Stress level tracks LOW/MEDIUM/HIGH but no cortisol biomarker. Alert text mentions cortisol but no modeling |
| 23 | **Insulin Resistance Risk** | Diabetes | **DONE** | `calculateInsulinResistance()` computes score from BMI + activity + disease data. Full formula implemented |
| 24 | **Stress Level Simulation** | Both | **DONE** | `calculateStressScore()` + stress type simulation fully working |

---

### 4. EXPECTED IMPACT ITEMS (from "Expected Impact")

| # | Claimed Impact | Status | Gap |
|---|---------------|--------|-----|
| 25 | Shift from Reactive to Proactive | **PARTIAL** | Simulation exists but is rule-based, not truly predictive. No time-series forecasting. Cannot predict "tomorrow's risk" |
| 26 | Democratizing Specialized Care | **PARTIAL** | Disease profiling works but explanations are generic KB extracts, not specialist-level AI reasoning |
| 27 | Behavioral Nudging | **PARTIAL** | Alerts exist but are static templates. No adaptive nudging based on user behavior patterns |
| 28 | Safe & Responsible AI | **MISSING** | No guardrails. No content filtering. No medical disclaimer system |
| 29 | Eliminating "Food Anxiety" | **PARTIAL** | Food impact shows risk change but no "certainty" — just a number change, not a reassuring explanation |

---

### 5. FUTURE SCOPE (from "Future Scope")

| # | Future Feature | Status |
|---|---------------|--------|
| 30 | Wearable device integration | **MISSING** |
| 31 | Real-time glucose monitoring | **MISSING** |
| 32 | Mobile application | **MISSING** (responsive web only) |
| 33 | Clinical dataset integration | **MISSING** |
| 34 | AI-based doctor recommendation | **MISSING** |
| 35 | Additional metabolic disorders | **PARTIAL** (dynamic disease system supports it, thyroid seeder exists) |

---

### 6. BONUS FEATURES (implemented but not in original proposal)

| # | Feature | Description |
|---|---------|-------------|
| B1 | **Dynamic Disease Catalog** | Database-driven disease definitions — add new diseases without code changes |
| B2 | **Full Admin Dashboard** | User management, simulation logs, alert management, reporting, RAG CRUD |
| B3 | **Simulation History & Replay** | Browse, filter, re-run previous simulations |
| B4 | **Admin Reports with Daily Trends** | Risk distribution, daily simulation counts, alert severity trends |
| B5 | **3D Character Visualization** | Three.js + React Three Fiber for onboarding/simulation UI |
| B6 | **Multi-disease Support** | User can have data for multiple diseases simultaneously |

---

## PART 2: CRITICAL MISSING FEATURES TO IMPLEMENT

**Priority: MUST-HAVE for hackathon demo (ranked by impact)**

### PRIORITY 1: Amazon Bedrock / LLM Integration (THE BIGGEST GAP)

The proposal's entire value proposition centers on AI-powered predictive simulation. Currently, the system is 100% rule-based.

**What to implement:**

1. **Bedrock Service** — Create `app/Services/AI/BedrockService.php`
   - Install AWS SDK: `composer require aws/aws-sdk-php`
   - Configure Bedrock client with Claude 3.5 Sonnet model
   - Create a `generateInsight(string $prompt, array $context): string` method
   - Add config: `config/services.php` → `bedrock.model_id`, `bedrock.region`, `bedrock.access_key`

2. **AI-Powered Simulation Explanations** — Replace static RAG text with LLM-generated insights
   - In `SimulationService`, after risk recalculation, call Bedrock with:
     ```
     Prompt: "Given this user's health profile: {snapshot}, they simulated: {type} with parameters: {params}.
     Original risk: {original}. New risk: {simulated}. Risk change: {change}.
     Disease context: {diseases}. Disease data: {disease_data}.
     Generate a 2-3 sentence personalized health insight explaining WHY the risk changed,
     referencing their specific metrics (sleep hours, stress level, blood sugar, etc.)"
     ```
   - Store LLM response as `rag_explanation`

3. **Personalized RAG with Bedrock** — Enhance `RagSearchService`
   - After keyword tree traversal finds relevant KB pages, send to Bedrock:
     ```
     "Based on this knowledge: {kb_content}
     And this user's profile: {health_profile}
     And their disease data: {disease_data}
     Answer their question: {question}
     Make the answer specific to their situation."
     ```
   - This gives personalized answers grounded in KB content

4. **Bedrock Guardrails** — Add safety filtering
   - Create `app/Services/AI/GuardrailService.php`
   - Apply Bedrock Guardrails (content filtering policy) to every LLM response
   - Add medical disclaimer: "This is for informational purposes only. Consult your doctor."
   - Filter out any text that sounds like a prescription or specific medication recommendation

### PRIORITY 2: Glucose Spike Prediction (Pre-Meal Flash Forward)

The "Flash Forward" is the headline feature of the proposal.

**What to implement:**

1. **Glycemic Index Database** — Create `database/seeders/GlycemicIndexSeeder.php`
   - Seed a `food_glycemic_data` table with columns: `food_item`, `glycemic_index`, `glycemic_load`, `typical_spike_mg_dl`, `peak_time_minutes`, `recovery_time_minutes`, `category`
   - Start with 50-100 common Indian foods

2. **Glucose Curve Prediction** — Enhance `SimulationService.simulateFoodImpact()`
   - Instead of flat +40/-15 adjustment, calculate a time-dependent glucose response:
     ```
     peak_glucose = baseline + (GI × load_factor × insulin_sensitivity_factor)
     peak_time = food.peak_time_minutes × (1 + stress_modifier)
     recovery = food.recovery_time_minutes × (1 - activity_modifier)
     ```
   - Return a `glucose_curve` array: `[{time: 0, glucose: 95}, {time: 30, glucose: 140}, {time: 60, glucose: 155}, {time: 120, glucose: 110}]`

3. **Cross-Factor Interaction** — Factor in user's current state:
   - Sleep < 6h → multiply spike by 1.3 (poor sleep = worse insulin sensitivity)
   - Stress = HIGH → multiply spike by 1.2
   - Physical activity = ACTIVE → multiply spike by 0.85
   - This makes the same food have different impacts for different users (the "non-linear" promise)

4. **Bedrock-Enhanced Food Explanation** — After computing curve, ask Bedrock:
   ```
   "The user with {profile} ate {food}. Predicted glucose peak: {peak} at {time}min.
   Their sleep was {hours}h (affecting insulin sensitivity).
   Generate a brief, friendly explanation of what will happen and a healthier alternative."
   ```

### PRIORITY 3: Chart.js Data Visualization

Judges need to SEE the data to be impressed.

**What to implement:**

1. Install Chart.js: `npm install chart.js`

2. **Dashboard Charts** (user dashboard)
   - **Risk Score Radar Chart** — 5-axis spider: metabolic, insulin, sleep, stress, diet
   - **Risk History Line Chart** — Plot all historical digital twin `overall_risk_score` values over time
   - **Simulation Impact Bar Chart** — Show before/after risk comparison for recent simulations

3. **Glucose Spike Curve** (food impact page)
   - **Line Chart** with time (x-axis) vs glucose level (y-axis)
   - Show predicted peak, safe zone (70-140 mg/dL shaded green), danger zone (>180 shaded red)
   - Overlay multiple foods for comparison

4. **Admin Dashboard Charts**
   - **Risk Distribution Pie Chart** — LOW/MODERATE/HIGH/CRITICAL
   - **Daily Simulations Line Chart** — Activity trend over 30 days
   - **Alerts by Severity Stacked Bar** — daily info/warning/critical

### PRIORITY 4: Real-time Risk Score Updates (Laravel Reverb)

**What to implement:**

1. Install Reverb: `composer require laravel/reverb && php artisan reverb:install`

2. **Create Events:**
   - `RiskScoreUpdated` → broadcasts when digital twin regenerates
   - `AlertTriggered` → broadcasts when new alert is created
   - `SimulationCompleted` → broadcasts when simulation finishes

3. **Dashboard WebSocket Listener** — Frontend subscribes to user's private channel
   - Risk score meter updates live when simulation runs in another tab
   - Alert badge count updates without page refresh
   - Toast notification on new critical alerts

### PRIORITY 5: Cortisol Modeling

**What to implement:**

1. Add `cortisol_score` to `digital_twins` table (migration)
2. Add `calculateCortisolScore()` to `RiskEngineService`:
   ```
   Base: stress_level mapping (HIGH=80, MEDIUM=50, LOW=20)
   + sleep deprivation factor (< 6h: +15)
   + physical inactivity bonus (+10 if sedentary)
   + disease interaction (PCOS: +10 due to HPA axis)
   ```
3. Surface in DigitalTwinResource and dashboard visualization

### PRIORITY 6: Meal Timing Effect

**What to implement:**

1. Add `meal_time` (optional) parameter to `FoodImpactRequest`
2. In `SimulationService.simulateFoodImpact()`:
   - Morning (6-10am): circadian insulin sensitivity HIGH → spike × 0.85
   - Afternoon (12-3pm): moderate → spike × 1.0
   - Evening (6-9pm): sensitivity LOW → spike × 1.15
   - Late night (10pm-2am): worst → spike × 1.35
3. Add to Bedrock prompt context: "They plan to eat this at {time}"

---

## PART 3: HACKATHON-WINNING IMPROVEMENTS

### A. Demo-Critical Enhancements

| # | Enhancement | Why It Wins | Effort |
|---|------------|-------------|--------|
| 1 | **Bedrock-powered "What If" with natural language output** | Judges see actual AI reasoning, not just numbers | High |
| 2 | **Glucose spike curve chart** | Visual WOW factor — a chart speaks louder than JSON | Medium |
| 3 | **Side-by-side food comparison** | "Pizza vs Salad tonight" — two curves on one chart | Medium |
| 4 | **User-specific RAG** | "You spiked after rice on March 3rd" — shows memory | Medium |
| 5 | **Voice-of-the-Twin** | Bedrock generates a conversational health summary: "Your Digital Twin says: Your sleep has been dropping. If you eat that pizza tonight, expect a 40% spike..." | Medium |
| 6 | **Medical disclaimer banner** | Shows responsible AI awareness — judges love this | Low |
| 7 | **Export/Share health report** | PDF export of digital twin + risk analysis | Medium |

### B. Technical Differentiators

| # | Differentiator | What to Build |
|---|---------------|---------------|
| 1 | **Non-linear Cross-Factor Engine** | Show that eating pizza after 8h sleep has DIFFERENT risk than after 5h sleep. This is the core "non-linear" claim. Build a demo scenario showing the same meal with two different sleep inputs yielding different glucose curves. |
| 2 | **Counterfactual Comparison** | Add a `POST /api/simulations/compare` endpoint accepting TWO scenarios. Returns side-by-side risk comparison: "Scenario A (pizza tonight) vs Scenario B (pizza tomorrow morning)" |
| 3 | **Simulation Playground** | Live sliders on the dashboard: drag sleep_hours (4→9), see risk score animate. Drag stress_level, see it change. This requires Reverb or polling but the visual impact is enormous. |
| 4 | **Indian Food Database** | Seed 100+ Indian foods with GI values (dal, roti, rice, biryani, samosa, gulab jamun, idli, dosa, upma, poha, etc.). This connects to the "AI for Bharat" mission. |
| 5 | **Vernacular Insights** | Add a `language` parameter to Bedrock prompts. Generate insights in Hindi/regional languages. Demonstrates accessibility for Tier-2/3 cities. |

### C. Polish & Presentation

| # | Polish Item | Impact |
|---|------------|--------|
| 1 | **Guided onboarding flow** — Step-by-step wizard with progress indicator | First impression |
| 2 | **Loading animation during simulation** — "Your Digital Twin is thinking..." with Three.js character animation | Engagement |
| 3 | **Risk color coding** — Green/Yellow/Orange/Red everywhere (scores, cards, charts) | Visual clarity |
| 4 | **Comparison view** — Show BEFORE and AFTER twin side by side after simulation | Understanding |
| 5 | **Alert toast notifications** — Slide-in alerts when critical risk detected | Urgency |
| 6 | **Demo mode / Guided tour** — Auto-walkthrough for judges showing every feature | Judging experience |
| 7 | **Landing page with clear value prop** — "See Your Health Before You Live It" with demo video | Hook |

### D. Scoring Rubric Optimization

If the hackathon judges on:

| Criteria | Current Score | After Fixes | What to Demonstrate |
|----------|:------------:|:-----------:|---------------------|
| **Innovation** | 6/10 | 9/10 | Bedrock-powered metabolic "Flight Simulator" with cross-factor glucose prediction |
| **Technical Complexity** | 7/10 | 9/10 | LLM + RAG + Vector/keyword search + real-time + risk engine + 5-score model |
| **Impact / Usefulness** | 6/10 | 8/10 | Indian food database, vernacular insights, PCOS+Diabetes dual coverage |
| **Completeness** | 7/10 | 9/10 | End-to-end flow: register → profile → disease → twin → simulate → alert → explain |
| **Presentation** | 5/10 | 8/10 | Charts, real-time updates, 3D twin, guided demo |
| **AI Usage** | 3/10 | 9/10 | Bedrock reasoning, guardrails, personalized RAG, explainable AI |
| **AWS Integration** | 1/10 | 8/10 | Bedrock, Guardrails, Knowledge Bases, potentially S3/CloudFront |

---

## PART 4: IMPLEMENTATION PRIORITY ORDER

For maximum hackathon impact, implement in this exact order:

```
PHASE 1 — AI Core (Highest Impact)
 ├── 1.1  Install AWS SDK + Bedrock config
 ├── 1.2  BedrockService.php (generateInsight method)
 ├── 1.3  Integrate into SimulationService (personalized explanations)
 ├── 1.4  Integrate into RagSearchService (LLM-enhanced answers)
 └── 1.5  Add GuardrailService + medical disclaimer

PHASE 2 — Visualization (Judges Need to See)
 ├── 2.1  Install Chart.js
 ├── 2.2  Dashboard radar chart (5 scores)
 ├── 2.3  Risk history line chart
 ├── 2.4  Glucose spike curve for food impact
 └── 2.5  Admin dashboard charts

PHASE 3 — Food Prediction Enhancement
 ├── 3.1  Food glycemic data table + Indian food seeder (100+ foods)
 ├── 3.2  Time-dependent glucose curve calculation
 ├── 3.3  Cross-factor interaction (sleep × stress × food)
 ├── 3.4  Meal timing effect
 └── 3.5  Food comparison endpoint

PHASE 4 — Real-time & Polish
 ├── 4.1  Laravel Reverb setup
 ├── 4.2  Risk score live broadcast events
 ├── 4.3  Alert toast notifications
 ├── 4.4  Cortisol score calculation
 └── 4.5  Demo mode / guided walkthrough

PHASE 5 — Stretch Goals
 ├── 5.1  PDF health report export
 ├── 5.2  Vernacular insights (Hindi)
 ├── 5.3  Counterfactual comparison endpoint
 └── 5.4  Simulation playground (live sliders)
```

---

## PART 5: CURRENT STATUS SUMMARY

| Category | Done | Partial | Missing | Total |
|----------|:----:|:-------:|:-------:|:-----:|
| Core Features (from proposal) | 0 | 4 | 1 | 5 |
| Architecture Components | 0 | 2 | 5 | 7 |
| Simulation Predictions | 3 | 6 | 3 | 12 |
| Impact Claims | 0 | 3 | 2 | 5 |
| Future Scope | 0 | 1 | 5 | 6 |
| **TOTAL** | **3** | **16** | **16** | **35** |

**Bottom Line:** The application has a solid foundation — clean architecture, well-structured code, comprehensive CRUD, dynamic disease system, and functional risk scoring. But the **AI/LLM core that defines the hackathon pitch is entirely absent**. The #1 priority is integrating Amazon Bedrock for AI-powered explanations and personalization. The #2 priority is data visualization (Chart.js) so judges can see the "Flight Simulator" metaphor come to life.
