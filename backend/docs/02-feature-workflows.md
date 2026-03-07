# HormoneLens — Feature Workflows

This document describes every user-facing and admin-facing workflow in the application, step by step.

---

## Table of Contents

1. [User Registration & Login](#1-user-registration--login)
2. [Onboarding — Health Profile Setup](#2-onboarding--health-profile-setup)
3. [Disease Data Collection](#3-disease-data-collection)
4. [Digital Twin Generation](#4-digital-twin-generation)
5. [Lifestyle Simulation (Meal / Sleep / Stress)](#5-lifestyle-simulation)
6. [Food Impact Simulation](#6-food-impact-simulation)
7. [RAG Knowledge Base Query](#7-rag-knowledge-base-query)
8. [Alert System](#8-alert-system)
9. [Simulation History & Replay](#9-simulation-history--replay)
10. [User Dashboard](#10-user-dashboard)
11. [Admin Dashboard](#11-admin-dashboard)
12. [Admin — User Management](#12-admin--user-management)
13. [Admin — Simulation Logs](#13-admin--simulation-logs)
14. [Admin — Alert Management](#14-admin--alert-management)
15. [Admin — Reports](#15-admin--reports)
16. [Admin — RAG Knowledge Base Management](#16-admin--rag-knowledge-base-management)

---

## 1. User Registration & Login

### Web Flow

```
Landing Page (/)
    │
    ├── Click "Register"
    │       │
    │       ▼
    │   Register Page (/register)
    │   Fields: name, email, password, password_confirmation
    │       │
    │       ▼
    │   POST /register → Validate → Create User → Login → Redirect to /onboarding
    │
    └── Click "Login"
            │
            ▼
        Login Page (/login)
        Fields: email, password
            │
            ▼
        POST /login → Authenticate
            │
            ├── Admin? → Redirect to /admin
            └── User?  → Redirect to /dashboard
```

### API Flow

```
POST /api/register
  Body: { name, email, password, password_confirmation }
  Response: { user, token } (201)

POST /api/login
  Body: { email, password }
  Response: { user, token } (200)
  Rate limited: 10 req/min per IP

POST /api/logout
  Header: Authorization: Bearer <token>
  Response: { message: "Logged out" } (200)
  Action: Revokes current Sanctum token
```

### Validation Rules

| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| email | required, email, unique:users |
| password | required, min:8, confirmed |

---

## 2. Onboarding — Health Profile Setup

After registration, users must complete their health profile before accessing the main application.

### Flow

```
User arrives at /onboarding (or POST /api/health-profile)
    │
    ▼
Fill Health Profile Form:
  - Gender (female / male)
  - Weight (20–300 kg)
  - Height (50–250 cm)
  - Average Sleep Hours (0–24)
  - Stress Level (low / medium / high)
  - Physical Activity (sedentary / moderate / active)
  - Eating Habits (freetext)
  - Water Intake (0–20 liters/day)
  - Disease Type (diabetes / pcod / etc.)
    │
    ▼
POST /api/health-profile
  → StoreHealthProfileRequest validation
  → HealthProfileRepository.create()
  → Return HealthProfileResource (201)
    │
    ▼
Redirect to Disease Data Collection
```

### Update Flow

```
PUT /api/health-profile
  → UpdateHealthProfileRequest (all fields are "sometimes", allowing partial updates)
  → HealthProfileRepository.update()
  → Return updated HealthProfileResource
```

---

## 3. Disease Data Collection

The disease system is fully **dynamic** — disease definitions, fields, and validation rules are stored in the database, not hardcoded.

### How Diseases Work

```
┌────────────┐      ┌─────────────┐      ┌────────────────┐
│  Disease    │ 1:N  │ DiseaseField│      │ UserDiseaseData│
│ (diabetes)  │─────▶│ (slug,label │      │ (field_values  │
│             │      │  field_type │      │   = JSON blob) │
└────────────┘      │  risk_config)│      └────────────────┘
                    └─────────────┘
```

### Disease Discovery Flow

```
GET /api/diseases
    │
    ▼
Returns all active diseases with their field definitions:
[
  {
    slug: "diabetes",
    name: "Diabetes",
    fields: [
      { slug: "avg_blood_sugar", label: "Average Blood Sugar", field_type: "number", options: null, is_required: true },
      { slug: "family_history", label: "Family History", field_type: "boolean", ... },
      { slug: "frequent_urination", label: "Frequent Urination", field_type: "select", options: ["often","occasionally","rarely"] },
      ...11 fields total
    ]
  },
  {
    slug: "pcod",
    name: "PCOD/PCOS",
    fields: [
      { slug: "cycle_regularity", label: "Cycle Regularity", field_type: "select", options: ["regular","irregular","missed"] },
      ...11 fields total
    ]
  }
]
```

### Data Submission Flow

```
User selects a disease (e.g., /disease/diabetes)
    │
    ▼
GET /api/diseases/diabetes
  → Returns disease definition + user's existing data (if any)
    │
    ▼
User fills out disease-specific fields
    │
    ▼
POST /api/diseases/diabetes
  Body: { field_values: { avg_blood_sugar: 150, family_history: true, frequent_urination: "often", ... } }
    │
    ▼
DiseaseController.store():
  1. Validates slug exists in active diseases
  2. Validates field_values against DiseaseField definitions (required fields, types, options)
  3. Casts values to correct types (boolean, integer, string)
  4. DiseaseRepository.createOrUpdate() → upserts UserDiseaseData
  5. Returns { message, data: field_values }
```

### Seeded Disease Fields

**Diabetes (11 fields):**
- avg_blood_sugar (number) — Average fasting blood sugar level
- family_history (boolean) — Family history of diabetes
- frequent_urination (select: often/occasionally/rarely)
- excessive_thirst (select: often/occasionally/rarely)
- fatigue (select: often/occasionally/rarely)
- blurred_vision (boolean)
- numbness_tingling (boolean)
- slow_wound_healing (boolean)
- unexplained_weight_loss (boolean)
- sugar_cravings (select: frequent/occasional/rare)
- energy_crashes_after_meals (select: often/occasionally/rarely)

**PCOD (11 fields):**
- cycle_regularity (select: regular/irregular/missed)
- excess_facial_body_hair (boolean)
- acne_oily_skin (boolean)
- hair_thinning (boolean)
- weight_gain_difficulty_losing (boolean)
- mood_swings_anxiety (select: often/occasionally/rarely)
- dark_skin_patches (boolean)
- fatigue_frequency (select: often/occasionally/rarely)
- sleep_disturbances (select: often/occasionally/rarely)
- sugar_cravings (select: frequent/occasional/rare)
- insulin_resistance_diagnosed (boolean)

Each field includes a `risk_config` JSON that defines how the field value impacts the three risk scores (metabolic, insulin, hormonal).

---

## 4. Digital Twin Generation

The **Digital Twin** is the centerpiece of the application — a calculated numerical representation of the user's current health state.

### Generation Flow

```
User clicks "Generate Digital Twin"
    │
    ▼
POST /api/digital-twin/generate
    │
    ▼
DigitalTwinService.generate(user):
    │
    ├── 1. Load HealthProfile
    ├── 2. Load all UserDiseaseData (keyed by disease slug)
    │
    ├── 3. Calculate 5 scores via RiskEngineService:
    │       ├── metabolic_health_score     = calculateMetabolicRisk(profile, diseaseMap)
    │       ├── insulin_resistance_score   = calculateInsulinResistance(profile, diseaseMap)
    │       ├── sleep_score                = calculateSleepScore(profile.avg_sleep_hours)
    │       ├── stress_score               = calculateStressScore(profile.stress_level)
    │       └── diet_score                 = calculateDietScore(profile, diseaseMap)
    │
    ├── 4. Calculate overall risk:
    │       overall_risk = (0.4 × metabolic) + (0.3 × insulin) + (0.3 × hormonal_imbalance)
    │
    ├── 5. Build snapshot_data JSON:
    │       {
    │         health_profile: { weight, height, sleep, stress, activity, water, eating, gender },
    │         disease_slugs: ["diabetes"],
    │         disease_data: { diabetes: { avg_blood_sugar: 150, ... } },
    │         scores: { metabolic, insulin, sleep, stress, diet },
    │         overall_risk_score: 62.5,
    │         risk_category: "HIGH",
    │         generated_at: "2026-03-07T..."
    │       }
    │
    ├── 6. Deactivate all previous twins:
    │       DigitalTwinRepository.deactivateAll(user_id) → sets is_active = false
    │
    └── 7. Create new active twin:
            DigitalTwinRepository.create({ ..., is_active: true })
    │
    ▼
Return DigitalTwinResource (201)
```

### Retrieve Twin

```
GET /api/digital-twin/active   → Current active twin
GET /api/digital-twin          → All historical twins (paginated)
GET /api/digital-twin/{id}     → Specific twin by ID (authorization check)
```

---

## 5. Lifestyle Simulation

Simulations answer "What if?" questions by modifying the digital twin snapshot and recalculating risk.

### Supported Simulation Types

| Type | What It Modifies | Example Input |
|------|-----------------|---------------|
| `meal` | Sugar cravings frequency in disease data | "What if I reduce sugar intake?" |
| `sleep` | `avg_sleep_hours` in health profile snapshot | `{ sleep_hours: 8 }` |
| `stress` | `stress_level` in health profile snapshot | `{ stress_level: "low" }` |

### Simulation Flow

```
POST /api/simulations/run
  Body: {
    type: "sleep",
    description: "What if I sleep 8 hours?",
    parameters: { sleep_hours: 8 }
  }
    │
    ▼
RunSimulationRequest validation:
  - type: required, in:meal,sleep,stress
  - description: required, max:500
  - parameters.sleep_hours: nullable, numeric (for sleep type)
  - parameters.stress_level: nullable, in:low,medium,high (for stress type)
    │
    ▼
SimulationService.simulateLifestyleChange(user, input):
    │
    ├── 1. Retrieve active Digital Twin
    │       (throws RuntimeException if none exists)
    │
    ├── 2. Copy snapshot_data as modifiedSnapshot
    │
    ├── 3. applyLifestyleModifier(type, modifiedSnapshot, parameters):
    │       ├── MEAL: sugar_cravings → "rare" in disease_data
    │       ├── SLEEP: avg_sleep_hours → parameters.sleep_hours
    │       └── STRESS: stress_level → parameters.stress_level
    │
    ├── 4. Recalculate risk from modified snapshot:
    │       RiskEngineService.recalculateFromSnapshot(modifiedSnapshot)
    │       → Returns new scores + overall_risk + risk_category
    │
    ├── 5. Calculate risk_change:
    │       risk_change = simulated_risk - original_risk
    │       (negative = improvement, positive = worsening)
    │
    ├── 6. Query RAG for explanation:
    │       RagSearchService.search(description, diseaseContext)
    │       → Returns contextual medical explanation
    │
    ├── 7. Store Simulation record:
    │       SimulationRepository.create({
    │         user_id, digital_twin_id, type,
    │         input_data, modified_twin_data,
    │         original_risk_score, simulated_risk_score, risk_change,
    │         risk_category_before, risk_category_after,
    │         rag_explanation, rag_confidence, results
    │       })
    │
    ├── 8. Evaluate alerts:
    │       AlertService.evaluate(user, simulationResult, simulationId)
    │       → Creates alerts if rules are triggered
    │
    └── 9. Return SimulationResource with alerts
```

### Retrieve Simulations

```
GET /api/simulations          → Paginated list (default 15/page)
GET /api/simulations/{id}     → Single simulation detail
```

---

## 6. Food Impact Simulation

A specialized simulation type that evaluates the glycemic impact of specific foods.

### Flow

```
POST /api/food-impact
  Body: { food_item: "white rice", quantity: "1 cup" }
    │
    ▼
FoodImpactRequest validation:
  - food_item: required, max:255
  - quantity: nullable
    │
    ▼
SimulationService.simulateFoodImpact(user, input):
    │
    ├── 1. Retrieve active Digital Twin
    │
    ├── 2. applyFoodModifier(modifiedSnapshot, food_item):
    │       - Checks if food is high-GI → increases blood sugar in snapshot
    │       - Low-GI → decreases blood sugar
    │
    ├── 3. Recalculate risk from modified snapshot
    │
    ├── 4. buildFoodAlternatives(food_item):
    │       - Suggests healthier food substitutes
    │
    ├── 5. Query RAG for food-related explanation
    │
    ├── 6. Store Simulation (type: FOOD_IMPACT)
    │
    ├── 7. Evaluate alerts (may trigger HIGH_GI alert)
    │
    └── 8. Return results + alternatives + alerts
```

---

## 7. RAG Knowledge Base Query

Users can ask free-form health questions and receive answers sourced from the curated knowledge base.

### Query Flow

```
POST /api/rag/query
  Body: { question: "What foods help manage insulin resistance?", disease_context: "diabetes" }
  Rate limited: 20 req/min
    │
    ▼
RagQueryRequest validation:
  - question: required, max:500
  - disease_context: nullable, in:diabetes,pcod
    │
    ▼
RagSearchService.searchAndLog(question, disease_context, user):
    │
    ├── 1. TOKENIZE:
    │       "What foods help manage insulin resistance?"
    │       → Remove 70+ stopwords (what, help, the, is, a, ...)
    │       → Keep tokens > 2 chars
    │       → Result: ["foods", "manage", "insulin", "resistance"]
    │
    ├── 2. FETCH ROOT NODES:
    │       Load all root nodes (depth=0) from all RagDocuments
    │       (or filter by disease_context if provided)
    │
    ├── 3. TRAVERSE TREE (greedy best-child):
    │       ┌─────────────────────────────────────────────┐
    │       │ For each root node:                         │
    │       │   score = count keyword matches with tokens │
    │       │   + disease context bonus (+2)              │
    │       │                                             │
    │       │ Pick highest-scoring root:                  │
    │       │   "Insulin Resistance" (score: 4)           │
    │       │                                             │
    │       │ Load children of winner, score each:        │
    │       │   "Diet & Insulin" (score: 3)  ← winner    │
    │       │   "Exercise & Insulin" (score: 1)           │
    │       │                                             │
    │       │ Continue descending until leaf or no        │
    │       │ children score > 0                          │
    │       └─────────────────────────────────────────────┘
    │
    ├── 4. BUILD ANSWER:
    │       Fetch RagPages for terminal nodes
    │       Concatenate page content
    │       Trim to 2000 characters
    │
    ├── 5. CALCULATE CONFIDENCE:
    │       confidence = 60 + (10 × depth_reached) + (5 × total_matches)
    │       Max: 95
    │
    ├── 6. LOG QUERY:
    │       RagQueryLog.create({
    │         user_id, question, reasoning_path,
    │         selected_nodes, confidence
    │       })
    │
    └── 7. RETURN:
            {
              answer: "Foods that help manage insulin resistance include...",
              reasoning_path: ["Diabetes KB", "Insulin Resistance", "Diet & Insulin"],
              source_nodes: [...],
              source_pages: [...],
              confidence: 0.85
            }
```

### Response Format (RagAnswerResource)

```json
{
  "answer": "string (up to 2000 chars)",
  "reasoning_path": ["Root Topic", "Child Topic", "Leaf Topic"],
  "source_nodes": [{ "id": 1, "title": "..." }],
  "source_pages": [{ "id": 1, "page_number": 1, "content": "..." }],
  "confidence": 0.85
}
```

---

## 8. Alert System

Alerts are automatically generated after each simulation based on predefined rules.

### Alert Evaluation Flow

```
After any simulation completes:
    │
    ▼
AlertService.evaluate(user, simulationResult, simulationId):
    │
    ├── Rule 1: RISK THRESHOLD
    │   IF simulated_risk_score > 75
    │   → Create Alert (type: RISK_THRESHOLD, severity: CRITICAL)
    │   → Title: "High Risk Alert"
    │
    ├── Rule 2: HIGH GI FOOD
    │   IF simulation type is FOOD_IMPACT AND food is high-glycemic
    │   → Create Alert (type: HIGH_GI, severity: WARNING)
    │   → Title: "High Glycemic Food Detected"
    │
    ├── Rule 3: LOW SLEEP
    │   IF modified sleep_hours < 6
    │   → Create Alert (type: LOW_SLEEP, severity: WARNING)
    │   → Title: "Low Sleep Hours"
    │
    ├── Rule 4: HIGH STRESS
    │   IF modified stress_level == "high"
    │   → Create Alert (type: HIGH_STRESS, severity: WARNING)
    │   → Title: "High Stress Detected"
    │
    └── Rule 5: REPEATED RISK
        IF user has 3+ high-risk simulations in last 7 days
        → Create Alert (type: REPEATED_RISK, severity: CRITICAL)
        → Title: "Repeated High Risk Pattern"
```

### Alert Management

```
GET /api/alerts                → Paginated alerts (default 20/page)
GET /api/alerts/unread-count   → { count: 5 }
PATCH /api/alerts/{id}/read    → Mark single alert as read
PATCH /api/alerts/read-all     → Mark all alerts as read
```

---

## 9. Simulation History & Replay

### History Browsing

```
GET /api/history
  Query params:
    - type: meal|sleep|stress|food_impact (optional filter)
    - date_from: YYYY-MM-DD (optional)
    - date_to: YYYY-MM-DD (optional)
    - per_page: int (default 15)
    │
    ▼
HistoryRepository.paginateByUser(user_id, filters)
    → Returns paginated HistoryResource collection
    → Each entry includes: type, input_data, risk scores, alerts_count, created_at
```

### History Detail

```
GET /api/history/{id}
    → Returns full simulation details (scoped to authenticated user)
    → Includes: input_data, modified_twin_data, risk comparison, rag_explanation, alerts
```

### Simulation Replay

```
POST /api/history/{id}/rerun
    │
    ▼
HistoryController.rerun(id):
    1. Load original simulation (scoped to user)
    2. Extract original type and input_data
    3. Re-run simulation with same parameters:
       SimulationService.simulateLifestyleChange(user, originalInput)
       or SimulationService.simulateFoodImpact(user, originalInput)
    4. Return new SimulationResource
    │
    ▼
Uses CURRENT digital twin state → shows how same action
would affect the user NOW vs. when it was first run
```

### Delete History

```
DELETE /api/history/{id}
    → Soft-checks ownership → Deletes simulation record
    → Returns 204 No Content
```

---

## 10. User Dashboard

### Web Dashboard Flow

```
GET /dashboard
    │
    ▼
PageController.dashboard():
    1. Load user's HealthProfile
    2. Load SimulationResults (aggregated scores)
    3. Compute display data:
       - Risk scores (metabolic, insulin, sleep, stress, diet)
       - Lifestyle data for charts
       - Disease-specific indicators
    4. Render dashboard.blade.php with computed data
```

### API: Current User

```
GET /api/user
    → Returns UserResource with:
       - id, name, email, is_admin
       - health_profile (whenLoaded)
       - disease_data (whenLoaded)
       - active_digital_twin (whenLoaded)
       - simulations (whenLoaded)
```

---

## 11. Admin Dashboard

### Flow

```
GET /api/admin/dashboard
    │
    ▼
Admin\DashboardController.__invoke():
    │
    ├── total_users         = UserRepository.totalCount()
    ├── new_users_7d        = UserRepository.newUsersCount(7)
    ├── simulations_total   = SimulationRepository.totalCount()
    ├── simulations_today   = SimulationRepository.todayCount()
    ├── simulations_week    = SimulationRepository.weekCount()
    ├── avg_risk_score      = DigitalTwinRepository.averageRiskScore()
    ├── risk_distribution   = DigitalTwinRepository.riskDistribution()
    │                         → { low: 12, moderate: 8, high: 5, critical: 2 }
    └── unread_alerts       = AlertRepository.totalUnreadCount()
    │
    ▼
Return DashboardSummaryResource
```

---

## 12. Admin — User Management

### Workflows

```
LIST USERS:
  GET /api/admin/users?search=john&is_admin=false&per_page=20
  → UserRepository.paginate(perPage, search, isAdmin)
  → Returns paginated UserResource collection

VIEW USER:
  GET /api/admin/users/{id}
  → Loads user with:
    - Health profile
    - Disease data
    - Active digital twin
    - Recent simulations (latest 10)
  → Returns UserResource

TOGGLE ADMIN:
  PATCH /api/admin/users/{id}/toggle-admin
  → Flips user.is_admin boolean
  → Returns updated UserResource
  → Cannot toggle self (prevents admin lockout)
```

---

## 13. Admin — Simulation Logs

### Workflow

```
LIST SIMULATIONS:
  GET /api/admin/simulations?type=meal&user_id=5&date_from=2026-03-01&search=sugar
  → SimulationRepository.paginateAll(filters)
  → Returns paginated SimulationResource collection with user info

VIEW SIMULATION:
  GET /api/admin/simulations/{id}
  → Loads simulation with user and alerts
  → Returns SimulationResource
```

### Supported Filters

| Filter | Type | Example |
|--------|------|---------|
| type | string (enum value) | `meal`, `sleep`, `stress`, `food_impact` |
| user_id | integer | `5` |
| date_from | date | `2026-03-01` |
| date_to | date | `2026-03-07` |
| search | string | searches in description |
| per_page | integer | default 15 |

---

## 14. Admin — Alert Management

### Workflow

```
LIST ALERTS:
  GET /api/admin/alerts?severity=critical&type=risk_threshold&is_read=false
  → AlertRepository.paginateAll(filters)
  → Returns paginated AlertResource collection

VIEW ALERT:
  GET /api/admin/alerts/{id}
  → Returns AlertResource with user info
```

### Supported Filters

| Filter | Type | Example |
|--------|------|---------|
| severity | string | `info`, `warning`, `critical` |
| type | string | `risk_threshold`, `high_gi`, `low_sleep`, `high_stress`, `repeated_risk` |
| user_id | integer | `5` |
| is_read | boolean | `true`, `false` |
| date_from | date | `2026-03-01` |
| date_to | date | `2026-03-07` |
| search | string | searches in title/message |
| per_page | integer | default 20 |

---

## 15. Admin — Reports

### Workflow

```
GET /api/admin/reports?period_days=30
    │
    ▼
ReportController.__invoke():
    │
    ├── period = request.period_days (default 30)
    ├── start_date = now - period days
    │
    ├── new_users = UserRepository.newUsersCount(period)
    ├── simulations = SimulationRepository.totalCount() (for period)
    ├── risk_distribution = DigitalTwinRepository.riskDistribution()
    │
    ├── daily_risk_scores = DigitalTwinRepository.dailyRiskScoresForPeriod(period)
    │   → Array of { date, avg_risk_score } for each day
    │
    ├── daily_simulations = SimulationRepository.dailyCountForPeriod(period)
    │   → Array of { date, count } for each day
    │
    └── daily_alerts = AlertRepository.dailySeverityCountForPeriod(period)
        → Array of { date, info, warning, critical } for each day
    │
    ▼
Return ReportResource:
{
  period: { days: 30, start: "2026-02-05", end: "2026-03-07" },
  new_users: 15,
  simulations: 120,
  risk_distribution: { low: 12, moderate: 8, high: 5, critical: 2 },
  daily_risk_scores: [...],
  daily_simulations: [...],
  daily_alerts_by_severity: [...]
}
```

---

## 16. Admin — RAG Knowledge Base Management

### Document Management

```
LIST DOCUMENTS:
  GET /api/admin/rag/documents
  → Returns all documents with node_count and page_count

CREATE DOCUMENT:
  POST /api/admin/rag/documents
  Body: { title: "Thyroid Knowledge Base", description: "..." }

UPDATE DOCUMENT:
  PUT /api/admin/rag/documents/{id}
  Body: { title: "Updated Title" }

DELETE DOCUMENT:
  DELETE /api/admin/rag/documents/{id}
  → Cascading: deletes all nodes and pages within the document

VIEW DOCUMENT TREE:
  GET /api/admin/rag/documents/{id}
  → Returns document with full hierarchical node tree:
    {
      id, title, description,
      nodes: [
        {
          id, title, summary, keywords, depth: 0,
          children: [
            { id, title, keywords, depth: 1,
              children: [...],
              pages: [{ id, page_number, content }]
            }
          ]
        }
      ]
    }
```

### Node Management

```
CREATE NODE:
  POST /api/admin/rag/nodes
  Body: { document_id: 1, parent_id: null, title: "Blood Sugar", summary: "...", keywords: "blood,sugar,glucose" }
  → Auto-computes depth: parent_id ? parent.depth + 1 : 0

UPDATE NODE:
  PUT /api/admin/rag/nodes/{id}
  Body: { title: "Updated Title", keywords: "new,keywords" }

DELETE NODE:
  DELETE /api/admin/rag/nodes/{id}
  → Recursively deletes all children and their pages
```

### Page Management

```
CREATE PAGE:
  POST /api/admin/rag/pages
  Body: { node_id: 5, page_number: 1, content: "Detailed content about blood sugar management..." }

UPDATE PAGE:
  PUT /api/admin/rag/pages/{id}
  Body: { content: "Updated content", page_number: 2 }

DELETE PAGE:
  DELETE /api/admin/rag/pages/{id}
```

---

## Complete User Journey (End-to-End)

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────────┐
│ Register │───▶│Onboarding│───▶│ Disease  │───▶│ Generate     │
│          │    │ (Profile) │    │  Data    │    │ Digital Twin │
└──────────┘    └──────────┘    └──────────┘    └──────┬───────┘
                                                       │
                    ┌──────────────────────────────────┘
                    ▼
    ┌──────────────────────────────────────────────┐
    │              Main Application                 │
    │                                               │
    │  ┌──────────┐  ┌───────────┐  ┌───────────┐ │
    │  │Dashboard │  │Simulations│  │   RAG     │ │
    │  │(scores,  │  │(meal,sleep│  │ Knowledge │ │
    │  │ charts)  │  │ stress,   │  │  Query    │ │
    │  └──────────┘  │ food)     │  └───────────┘ │
    │                └─────┬─────┘                 │
    │                      │                       │
    │                      ▼                       │
    │  ┌──────────┐  ┌───────────┐                │
    │  │  Alerts  │◀─│  Alert    │                │
    │  │  Inbox   │  │ Engine    │                │
    │  └──────────┘  └───────────┘                │
    │                                              │
    │  ┌──────────┐                               │
    │  │ History  │  ← View / Replay / Delete     │
    │  └──────────┘                               │
    └──────────────────────────────────────────────┘
```
