# HormoneLens — Complete System Architecture Plan

**TL;DR** — A 10-module Laravel 12 API backend for a metabolic health simulator targeting Indian PCOS/Diabetes users. Uses Sanctum auth, MySQL, service+repository pattern, and a lightweight keyword-based tree RAG engine (no vectors). The system creates a "Digital Twin" from health profile + disease data, runs lifestyle/food simulations by cloning twin state and recalculating risk scores, and generates alerts. Admin APIs aggregate user/risk/simulation data. All REST, all stateless, all Flutter-ready.

---

## 1. Folder Structure

```
app/
├── Contracts/
│   ├── RagSearchInterface.php
│   └── RagTraversalInterface.php
├── Enums/
│   ├── DiseaseType.php            // diabetes, pcod
│   ├── RiskCategory.php           // low, moderate, high, critical
│   ├── SimulationType.php         // meal, sleep, stress, food_impact
│   ├── AlertType.php              // risk_threshold, high_gi, low_sleep, high_stress, repeated_risk
│   └── StressLevel.php            // low, medium, high
├── Events/
│   ├── DigitalTwinCreated.php
│   ├── SimulationCompleted.php
│   └── AlertTriggered.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── RegisterController.php
│   │   │   ├── LoginController.php
│   │   │   └── LogoutController.php
│   │   ├── HealthProfileController.php
│   │   ├── DiseaseDiabetesController.php
│   │   ├── DiseasePcodController.php
│   │   ├── DigitalTwinController.php
│   │   ├── SimulationController.php
│   │   ├── FoodImpactController.php
│   │   ├── AlertController.php
│   │   ├── HistoryController.php
│   │   ├── RagController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── UserManagementController.php
│   │       ├── SimulationLogController.php
│   │       ├── AlertManagementController.php
│   │       └── ReportController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── RegisterRequest.php
│   │   │   └── LoginRequest.php
│   │   ├── StoreHealthProfileRequest.php
│   │   ├── UpdateHealthProfileRequest.php
│   │   ├── StoreDiabetesRequest.php
│   │   ├── StorePcodRequest.php
│   │   ├── RunSimulationRequest.php
│   │   ├── FoodImpactRequest.php
│   │   └── RagQueryRequest.php
│   ├── Resources/
│   │   ├── UserResource.php
│   │   ├── HealthProfileResource.php
│   │   ├── DiabetesResource.php
│   │   ├── PcodResource.php
│   │   ├── DigitalTwinResource.php
│   │   ├── SimulationResource.php
│   │   ├── AlertResource.php
│   │   ├── HistoryResource.php
│   │   ├── RagAnswerResource.php
│   │   └── Admin/
│   │       ├── DashboardSummaryResource.php
│   │       ├── RiskDistributionResource.php
│   │       └── ReportResource.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Jobs/
│   ├── GenerateDigitalTwinJob.php
│   ├── RunSimulationJob.php
│   └── ProcessRagQueryJob.php
├── Models/
│   ├── User.php
│   ├── HealthProfile.php
│   ├── DiseaseDiabetes.php
│   ├── DiseasePcod.php
│   ├── DigitalTwin.php
│   ├── Simulation.php
│   ├── Alert.php
│   ├── RagDocument.php
│   ├── RagNode.php
│   ├── RagPage.php
│   └── RagQueryLog.php
├── Repositories/
│   ├── UserRepository.php
│   ├── HealthProfileRepository.php
│   ├── DiabetesRepository.php
│   ├── PcodRepository.php
│   ├── DigitalTwinRepository.php
│   ├── SimulationRepository.php
│   ├── AlertRepository.php
│   ├── HistoryRepository.php
│   └── Rag/
│       ├── RagDocumentRepository.php
│       ├── RagNodeRepository.php
│       └── RagPageRepository.php
├── Services/
│   ├── DigitalTwin/
│   │   └── DigitalTwinService.php
│   ├── Risk/
│   │   └── RiskEngineService.php
│   ├── Simulation/
│   │   └── SimulationService.php
│   ├── Alerts/
│   │   └── AlertService.php
│   └── Rag/
│       ├── RagSearchService.php         // implements RagSearchInterface
│       ├── RagTraversalEngine.php       // implements RagTraversalInterface
│       ├── RagScoringService.php
│       ├── RagAnswerBuilder.php
│       └── RagConfidenceService.php
└── Providers/
    └── RagServiceProvider.php           // binds interfaces to implementations

database/
├── migrations/
│   ├── 0001_create_users_table.php
│   ├── 0002_create_health_profiles_table.php
│   ├── 0003_create_disease_diabetes_table.php
│   ├── 0004_create_disease_pcod_table.php
│   ├── 0005_create_digital_twins_table.php
│   ├── 0006_create_simulations_table.php
│   ├── 0007_create_alerts_table.php
│   ├── 0008_create_rag_documents_table.php
│   ├── 0009_create_rag_nodes_table.php
│   ├── 0010_create_rag_pages_table.php
│   └── 0011_create_rag_query_logs_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── AdminUserSeeder.php
    ├── DiabetesRagSeeder.php
    ├── PcodRagSeeder.php
    └── LifestyleNutritionRagSeeder.php

routes/
└── api.php
```

---

## 2. Database Relationships (ER)

```
users
  ├── 1:1 → health_profiles        (user_id FK)
  ├── 1:1 → disease_diabetes       (user_id FK, nullable — only if condition=diabetes)
  ├── 1:1 → disease_pcod           (user_id FK, nullable — only if condition=pcod)
  ├── 1:N → digital_twins          (user_id FK; latest = active twin)
  ├── 1:N → simulations            (user_id FK)
  ├── 1:N → alerts                 (user_id FK)
  └── 1:N → rag_query_logs         (user_id FK)

rag_documents
  └── 1:N → rag_nodes              (document_id FK)

rag_nodes
  ├── self-referencing → parent_id  (nullable, tree hierarchy)
  └── 1:N → rag_pages              (node_id FK)

simulations
  └── N:1 → digital_twins          (digital_twin_id FK — snapshot reference)
```

**Table Definitions:**

| Table | Key Columns | Notes |
|---|---|---|
| `users` | id, name, email, password, is_admin (bool, default false), timestamps | Standard Laravel auth + admin flag |
| `health_profiles` | id, user_id (unique FK), weight, height, avg_sleep_hours, stress_level (enum: low/medium/high), physical_activity (enum: sedentary/moderate/active), eating_habits (text), water_intake, disease_type (enum: diabetes/pcod), timestamps | One per user |
| `disease_diabetes` | id, user_id (unique FK), avg_blood_sugar, family_history (bool), frequent_urination (enum: often/occasionally/rarely), excessive_thirst (enum), fatigue (enum), blurred_vision (enum), numbness_tingling (bool), slow_wound_healing (bool), unexplained_weight_loss (bool), sugar_cravings (enum: frequent/occasional/rare), energy_crashes_after_meals (bool), timestamps | Only for diabetes users |
| `disease_pcod` | id, user_id (unique FK), cycle_regularity (enum: regular/irregular/missed), avg_cycle_length_days, excess_facial_body_hair (bool), acne_oily_skin (bool), hair_thinning (bool), weight_gain_difficulty_losing (bool), mood_swings_anxiety (bool), dark_skin_patches (bool), fatigue_frequency (enum), sleep_disturbances (enum), sugar_cravings (enum), insulin_resistance_diagnosed (bool), timestamps | Only for PCOD users |
| `digital_twins` | id, user_id (FK), metabolic_health_score, insulin_resistance_score, sleep_score, stress_score, diet_score, overall_risk_score, risk_category (enum: low/moderate/high/critical), snapshot_data (JSON — frozen health profile+disease data at creation), is_active (bool), timestamps | Latest active twin used for simulations |
| `simulations` | id, user_id (FK), digital_twin_id (FK), type (enum: meal/sleep/stress/food_impact), input_data (JSON), modified_twin_data (JSON), original_risk_score, simulated_risk_score, risk_change, risk_category_before, risk_category_after, rag_explanation (text nullable), rag_confidence (decimal nullable), results (JSON), timestamps | Stores every simulation run |
| `alerts` | id, user_id (FK), simulation_id (FK nullable), type (enum), title, message, severity (enum: info/warning/critical), is_read (bool default false), timestamps | Per-user alerts |
| `rag_documents` | id, title, description, timestamps | 3 documents seeded |
| `rag_nodes` | id, document_id (FK), parent_id (nullable self-FK), title, summary, keywords (comma-separated string), depth (int), timestamps | Tree hierarchy |
| `rag_pages` | id, node_id (FK), page_number (int), content (longText), timestamps | Leaf content |
| `rag_query_logs` | id, user_id (FK), question, reasoning_path (JSON), selected_nodes (JSON), confidence (decimal), created_at | Audit trail |

---

## 3. Service Contracts (Interfaces)

**`App\Contracts\RagSearchInterface`**
- `search(string $question, ?string $diseaseContext): RagAnswerDTO`
  - Orchestrates the full RAG pipeline. Returns structured answer.

**`App\Contracts\RagTraversalInterface`**
- `traverse(Collection $rootNodes, array $tokens, ?string $diseaseContext): TraversalResult`
  - Walks the node tree from scored roots, returns best path + terminal nodes.

These two interfaces allow swapping to a vector-based implementation later without changing any consuming service.

---

## 4. Data Flow Diagrams

### 4a. Digital Twin Creation Flow

```
User submits health profile + disease data
         │
         ▼
HealthProfileController::store()
         │
         ├─► HealthProfileRepository::create()  → saves health_profiles row
         ├─► DiabetesRepository::create() OR PcodRepository::create()  → saves disease row
         │
         ▼
DigitalTwinService::generate(User $user)
         │
         ├─► Loads health_profiles + disease_diabetes/disease_pcod
         ├─► RiskEngineService::calculateMetabolicRisk()
         ├─► RiskEngineService::calculateInsulinResistance()
         ├─► RiskEngineService::calculateHormonalImbalance()
         ├─► Computes sleep_score, stress_score, diet_score
         ├─► RiskEngineService::categorizeRisk(overall_score)
         ├─► Snapshots current profile+disease data as JSON
         │
         ▼
DigitalTwinRepository::create() → saves digital_twins row (is_active=true)
         │
         ▼
Returns DigitalTwinResource to Flutter
```

### 4b. Simulation Engine Flow (Lifestyle Change)

```
User clicks "Meal Planning / Sleep / Stress" + enters input
         │
         ▼
POST /api/simulations/lifestyle
         │
         ▼
SimulationController::runLifestyle(RunSimulationRequest)
         │
         ▼
SimulationService::simulateLifestyleChange(User, input)
         │
         ├─► 1. Load active DigitalTwin (latest where is_active=true)
         ├─► 2. Clone twin snapshot_data into working copy
         ├─► 3. Apply modifier to working copy based on input type:
         │      - Meal: adjust diet_score factors
         │      - Sleep: adjust sleep_hours → sleep_score
         │      - Stress: adjust stress_level → stress_score
         ├─► 4. RiskEngineService::recalculate(modifiedData) → new scores
         ├─► 5. Compute delta: original_risk vs simulated_risk
         ├─► 6. AlertService::evaluate(user, simulationResult) → generate alerts if thresholds crossed
         ├─► 7. RagSearchService::search(input, diseaseContext) → get explanation
         ├─► 8. SimulationRepository::create() → persist simulation record
         │
         ▼
Returns SimulationResource (scores, deltas, alerts, RAG explanation)
```

### 4c. Food Impact Simulation Flow

```
User enters food item (e.g., "white rice")
         │
         ▼
POST /api/food/simulate
         │
         ▼
FoodImpactController::simulate(FoodImpactRequest)
         │
         ▼
SimulationService::simulateFoodImpact(User, foodItem)
         │
         ├─► 1. Load active DigitalTwin
         ├─► 2. RagSearchService::search("impact of {food} on {disease}") → nutritional context
         ├─► 3. Apply food-specific modifiers (glycemic impact estimation from RAG pages)
         ├─► 4. RiskEngineService::recalculate(modifiedData)
         ├─► 5. AlertService::evaluate() → e.g. "High glycemic food may spike blood sugar"
         ├─► 6. Build alternatives from RAG answer
         ├─► 7. SimulationRepository::create(type: food_impact)
         │
         ▼
Returns SimulationResource + alerts + alternatives + portion guidance
```

---

## 5. RAG Traversal Logic

```
Input: question = "How does sugar affect insulin resistance in diabetes?"

Step 1 — Tokenize
  tokens = ["sugar", "affect", "insulin", "resistance", "diabetes"]
  (strip stopwords: "how", "does", "in")

Step 2 — Score Root Nodes (depth=0)
  For each root node in rag_nodes WHERE parent_id IS NULL:
    score = countKeywordMatches(node.keywords, tokens)
    if diseaseContext == "diabetes": bonus +2 if node.keywords contains "diabetes"
  Sort descending. Pick top node.

Step 3 — Traverse Down
  currentNode = bestRoot
  path = [bestRoot]
  WHILE currentNode has children:
    children = rag_nodes WHERE parent_id = currentNode.id
    bestChild = child with highest keyword score
    IF bestChild.score <= currentNode.score:
      BREAK  // no child scores higher → stop
    currentNode = bestChild
    path.append(bestChild)

Step 4 — Fetch Pages
  terminalNodes = last 1-2 nodes in path
  pages = rag_pages WHERE node_id IN terminalNodes ORDER BY page_number

Step 5 — Build Answer
  Concatenate page contents, trim to relevant excerpts
  Wrap in structured response

Step 6 — Confidence
  confidence = min(95, 60 + (10 × path.length) + (5 × totalKeywordMatches))

Return:
{
  "answer": "...",
  "reasoning_path": ["Root: Diabetes Metabolic Health", "→ Insulin Resistance", "→ Sugar Impact"],
  "source_nodes": [node_ids],
  "source_pages": [page_ids],
  "confidence": "85%"
}
```

---

## 6. Risk Formula Outline

**`RiskEngineService`** — all scores normalized 0–100.

### `calculateMetabolicRisk(HealthProfile $hp, ?DiseaseDiabetes $d, ?DiseasePcod $p): float`
```
Base = 50
if sleep < 6h:     +15
if sleep 6-7h:     +5
if stress == high:  +20
if stress == medium: +10
if activity == sedentary: +15
if activity == moderate:  +5
if water_intake < 2L:     +5

// Disease modifiers
if diabetes:
  if avg_blood_sugar > 200: +20
  if avg_blood_sugar > 140: +10
  if family_history: +5
  if sugar_cravings == frequent: +5

if pcod:
  if cycle == irregular: +10
  if cycle == missed: +15
  if insulin_resistance_diagnosed: +15
  if weight_gain: +10

Clamp to 0–100
```

### `calculateInsulinResistance(profile, disease): float`
```
Base = 30
BMI = weight / (height_m²)
if BMI > 30: +25
if BMI 25-30: +15
if diabetes && avg_blood_sugar > 140: +20
if pcod && insulin_resistance_diagnosed: +25
if activity == sedentary: +10
if sugar_cravings == frequent: +10
Clamp to 0–100
```

### `calculateHormonalImbalance(profile, disease): float`
```
Base = 20
if pcod:
  +10 per true symptom (acne, hair_thinning, excess_hair, dark_patches, mood_swings)
  if cycle == missed: +15
  if sleep_disturbances == often: +10
if diabetes:
  if energy_crashes: +10
  if fatigue == often: +10
if stress == high: +15
if sleep < 6h: +10
Clamp to 0–100
```

### `categorizeRisk(score): RiskCategory`
```
0–30:  low
31–55: moderate
56–75: high
76–100: critical
```

### Overall scores for Digital Twin:
- `sleep_score` = inverse of sleep deficiency (0–100, higher = better)
- `stress_score` = inverse of stress level (0–100, higher = better)
- `diet_score` = computed from eating habits + disease food factors (0–100)
- `overall_risk_score` = weighted: metabolic 40% + insulin 30% + hormonal 30%

---

## 7. Alert Trigger Logic

**`AlertService::evaluate(User, SimulationResult): Collection<Alert>`**

| Condition | Alert Type | Severity | Example Message |
|---|---|---|---|
| `simulated_risk_score > 75` | `risk_threshold` | critical | "Your simulated risk score exceeded safe threshold." |
| Food is high-glycemic (from RAG) | `high_gi` | warning | "High glycemic food may cause a spike in blood sugar." |
| `sleep_hours < 6` in simulation | `low_sleep` | warning | "Sleep below 6h increases cortisol and metabolic risk." |
| `stress_level == high` in simulation | `high_stress` | warning | "High stress elevates cortisol, worsening insulin resistance." |
| 3+ high-risk simulations in 7 days | `repeated_risk` | critical | "You've had multiple high-risk simulations recently." |

---

## 8. Admin Aggregation Logic

**`GET /api/admin/dashboard`** — `AdminDashboardController::index()`
- Total users count, new users (last 7 days)
- Total simulations (today / this week / all-time)
- Risk distribution: `SELECT risk_category, COUNT(*) FROM digital_twins WHERE is_active=true GROUP BY risk_category`
- Unread alerts count
- Average metabolic risk score across all active twins

**`GET /api/admin/users`** — paginated user list with latest twin risk_category, simulation count, last active timestamp

**`GET /api/admin/risk-distribution`** — grouped counts by `risk_category` + optional date-range filter on digital_twins.created_at

**`GET /api/admin/simulation-logs`** — paginated simulation records with user info, filterable by type/date/risk_category

**`GET /api/admin/alerts`** — all alerts across users, filterable by severity/type/read-status

**`GET /api/admin/reports`** — aggregated trends: avg risk score per day (last 30 days), simulation count per day, alert count per severity per day

---

## 9. API Route Map

```
// Public
POST   /api/auth/register
POST   /api/auth/login

// Authenticated (Sanctum)
POST   /api/auth/logout
GET    /api/profile
PUT    /api/profile

// Health Profile & Disease
POST   /api/profile/health
PUT    /api/profile/health
POST   /api/profile/diabetes
PUT    /api/profile/diabetes
POST   /api/profile/pcod
PUT    /api/profile/pcod

// Digital Twin
POST   /api/digital-twin/generate
GET    /api/digital-twin
GET    /api/digital-twin/{id}

// Simulations
POST   /api/simulations/lifestyle
GET    /api/simulations
GET    /api/simulations/{id}

// Food Impact
POST   /api/food/simulate

// Alerts
GET    /api/alerts
PUT    /api/alerts/{id}/read
GET    /api/alerts/unread-count

// History
GET    /api/history
GET    /api/history/{id}
POST   /api/history/{id}/rerun

// RAG
POST   /api/rag/query

// Admin (Sanctum + AdminMiddleware)
GET    /api/admin/dashboard
GET    /api/admin/users
GET    /api/admin/risk-distribution
GET    /api/admin/simulation-logs
GET    /api/admin/alerts
GET    /api/admin/reports
```

---

## 10. Module Generation Order

| Batch | Modules | Dependencies |
|---|---|---|
| **Batch 1** | MODULE 1 (Auth) + MODULE 2 (Health Profile & Disease Inputs) | None — foundational |
| **Batch 2** | MODULE 4 (Risk Engine) + MODULE 3 (Digital Twin) | Needs models from Batch 1 |
| **Batch 3** | MODULE 9 (RAG Engine) + MODULE 10 (RAG Seeders) | Standalone, needed by simulations |
| **Batch 4** | MODULE 5 (Simulation Engine) + MODULE 6 (Alerts) | Needs Twin, Risk, RAG |
| **Batch 5** | MODULE 7 (History) + MODULE 8 (Admin) | Needs all prior data |

---

## Verification Checklist

- Run `php artisan migrate` — all 11 tables created with correct FKs
- Run `php artisan db:seed` — 3 RAG documents with 50+ pages populated
- Test auth: register → login → get token → access protected routes
- Test flow: create health profile → submit diabetes/pcod data → generate twin → run simulation → verify alerts generated → check history
- Test RAG: `POST /api/rag/query` with diabetes question → verify structured answer with confidence
- Test admin: login as admin → verify dashboard aggregates, user list, simulation logs
- Run `php artisan test` — all feature + unit tests pass

---

## Key Decisions

- **ai_prompt.txt takes precedence** over design.md: Laravel 12, MySQL, API-only (no Blade), PageIndex RAG (no ChromaDB/vectors)
- **Digital Twin as snapshot model**: each generation creates a new row with frozen `snapshot_data` JSON, so simulations reference a stable baseline
- **Queue for heavy work**: Digital Twin generation and simulations dispatch via `database` queue driver (Jobs), but controllers can also run synchronously for hackathon simplicity — queue is opt-in
- **is_admin flag on users table** rather than a separate roles/permissions system — sufficient for hackathon scope
- **Risk formulas are deterministic and rule-based** — no ML model needed; formulas can be tuned via config values later

---

**Architecture plan complete. Ready for "Generate Module X" instructions.**
