# HormoneLens — Architecture Overview

## 1. What is HormoneLens?

HormoneLens is a **health-risk simulation platform** that creates a personalized **Digital Twin** of a user's metabolic and hormonal health. Users provide their health profile and disease-specific data (Diabetes, PCOD, etc.), and the system calculates risk scores, runs "what-if" lifestyle simulations, generates health alerts, and answers medical knowledge queries through a built-in **RAG (Retrieval-Augmented Generation)** knowledge base.

The application serves two personas:
- **End Users** — track health, run simulations, receive alerts, query the knowledge base.
- **Administrators** — monitor aggregate health data, manage users, curate the RAG knowledge base, and generate reports.

---

## 2. Tech Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Laravel 11.x (PHP 8.2+) |
| **Authentication** | Laravel Sanctum (API tokens) + Session-based (Web) |
| **Database** | SQLite (dev) / MySQL / PostgreSQL (configurable) |
| **Frontend** | Blade templates + Vite (asset bundling) |
| **API Format** | JSON REST API |
| **Rate Limiting** | Built-in Laravel RateLimiter (3 tiers) |
| **Testing** | PHPUnit |

---

## 3. Design Patterns & Principles

### 3.1 Layered Architecture

```
┌──────────────────────────────────────────────────────────┐
│                     Routes (api.php / web.php)            │
├──────────────────────────────────────────────────────────┤
│ Middleware: auth:sanctum, admin, throttle, guest          │
├──────────────────────────────────────────────────────────┤
│          Controllers (Auth / API / Web / Admin)           │
│  Validate → Delegate → Return Resource                   │
├──────────────────────────────────────────────────────────┤
│                    Services (Business Logic)              │
│  RiskEngineService · DigitalTwinService                   │
│  SimulationService · AlertService · RagSearchService      │
├──────────────────────────────────────────────────────────┤
│                  Repositories (Data Access)               │
│  UserRepo · HealthProfileRepo · DiseaseRepo               │
│  DigitalTwinRepo · SimulationRepo · AlertRepo             │
│  HistoryRepo · RagDocumentRepo · RagNodeRepo · RagPageRepo│
├──────────────────────────────────────────────────────────┤
│              Models (Eloquent ORM) + Enums                │
├──────────────────────────────────────────────────────────┤
│                      Database (SQL)                       │
└──────────────────────────────────────────────────────────┘
```

### 3.2 Key Patterns

| Pattern | Usage |
|---------|-------|
| **Repository Pattern** | All database access goes through repository classes. Controllers never query Eloquent directly. |
| **Service Layer** | All business logic (risk calculation, simulation, alerting, RAG search) lives in dedicated service classes. Controllers stay thin. |
| **Contract/Interface Binding** | `RagSearchInterface` → `RagSearchService` and `RagTraversalInterface` → `RagTraversalEngine` are bound in `RagServiceProvider`, enabling testability and swappability. |
| **Enum-driven Domain** | 9 PHP enums model categorical data: risk levels, severity, alert types, simulation types, lifestyle attributes. |
| **API Resources** | All API responses are transformed through Laravel API Resource classes for consistent JSON structure. |
| **Dynamic Disease Catalog** | Disease definitions and fields are database-driven (not hardcoded), allowing new diseases to be added without code changes. |
| **JSON Snapshot Architecture** | Digital twins store a complete `snapshot_data` JSON blob capturing the user's state at generation time, enabling historical comparison and simulation replay. |

---

## 4. Directory Structure

```
app/
├── Console/Commands/          # Artisan commands (empty)
├── Contracts/                 # Interfaces
│   ├── RagSearchInterface.php
│   └── RagTraversalInterface.php
├── Enums/                     # 9 PHP enums
│   ├── AlertType.php          # RISK_THRESHOLD, HIGH_GI, LOW_SLEEP, HIGH_STRESS, REPEATED_RISK
│   ├── CravingFrequency.php   # FREQUENT, OCCASIONAL, RARE
│   ├── CycleRegularity.php    # REGULAR, IRREGULAR, MISSED
│   ├── Frequency.php          # OFTEN, OCCASIONALLY, RARELY
│   ├── PhysicalActivity.php   # SEDENTARY, MODERATE, ACTIVE
│   ├── RiskCategory.php       # LOW, MODERATE, HIGH, CRITICAL (with fromScore())
│   ├── Severity.php           # INFO, WARNING, CRITICAL
│   ├── SimulationType.php     # MEAL, SLEEP, STRESS, FOOD_IMPACT
│   └── StressLevel.php        # LOW, MEDIUM, HIGH
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # RegisterController, LoginController, LogoutController (API)
│   │   ├── Admin/             # DashboardController, UserMgmt, SimulationLog, AlertMgmt, RagMgmt, Report
│   │   ├── Web/               # AuthController, PageController, Admin/PageController (Blade views)
│   │   ├── AlertController.php
│   │   ├── DiseaseController.php
│   │   ├── DigitalTwinController.php
│   │   ├── FoodImpactController.php
│   │   ├── HealthProfileController.php
│   │   ├── HistoryController.php
│   │   ├── RagController.php
│   │   └── SimulationController.php
│   ├── Middleware/
│   │   └── AdminMiddleware.php
│   ├── Requests/              # 7 form request validators
│   └── Resources/             # 10 API resource transformers
├── Models/                    # 13 Eloquent models
├── Providers/
│   ├── AppServiceProvider.php # Rate limiters
│   └── RagServiceProvider.php # Interface bindings
├── Repositories/              # 10 repository classes
│   ├── Rag/                   # RagDocument, RagNode, RagPage repos
│   └── ...                    # User, HealthProfile, Disease, DigitalTwin, Simulation, Alert, History
└── Services/
    ├── Alerts/AlertService.php
    ├── DigitalTwin/DigitalTwinService.php
    ├── Rag/                   # RagSearchService, RagTraversalEngine, RagScoringService, RagAnswerBuilder, RagConfidenceService
    ├── Risk/RiskEngineService.php
    └── Simulation/SimulationService.php
```

---

## 5. Database Schema

### 5.1 Entity Relationship Diagram (Conceptual)

```
                          ┌───────────┐
                          │   User    │
                          │ (users)   │
                          └─────┬─────┘
              ┌────────┬────────┼────────┬──────────┬───────────┐
              ▼        ▼        ▼        ▼          ▼           ▼
        ┌──────────┐ ┌──────┐ ┌─────────┐ ┌──────────┐ ┌────────────┐
        │  Health  │ │User  │ │Digital  │ │Simulation│ │   Alert    │
        │ Profile  │ │Disease││  Twin   │ │          │ │            │
        │  (1:1)   │ │ Data │ │  (1:N)  │ │  (1:N)   │ │   (1:N)    │
        └──────────┘ │(1:N)│ └────┬────┘ └────┬─────┘ └────────────┘
                     └──┬───┘     │            │
                        │         │            │
                     ┌──▼───┐     │         ┌──▼───────────┐
                     │Disease│    ▼         │ RagQueryLog  │
                     │       │  ┌────────┐  │   (1:N)      │
                     └──┬────┘  │Sim.    │  └──────────────┘
                        │       │Result  │
                     ┌──▼────┐  └────────┘
                     │Disease│
                     │ Field │
                     └───────┘

        ┌──────────────┐
        │ RagDocument  │
        └──────┬───────┘
               │ 1:N
        ┌──────▼───────┐
        │   RagNode    │──┐
        │  (tree)      │  │ self-referential
        └──────┬───────┘◄─┘  (parent_id)
               │ 1:N
        ┌──────▼───────┐
        │   RagPage    │
        └──────────────┘
```

### 5.2 Table Definitions

#### Core Tables

| Table | Columns | Notes |
|-------|---------|-------|
| `users` | id, name, email (unique), password, is_admin (bool), timestamps | Standard auth + admin flag |
| `health_profiles` | id, user_id (unique FK), gender, weight, height, avg_sleep_hours, stress_level (enum), physical_activity (enum), eating_habits, water_intake, disease_type, timestamps | One per user; lifestyle baseline |
| `digital_twins` | id, user_id (FK), metabolic_health_score, insulin_resistance_score, sleep_score, stress_score, diet_score, overall_risk_score, risk_category (enum), snapshot_data (JSON), is_active (bool), timestamps | Versioned health snapshots |
| `simulations` | id, user_id (FK), digital_twin_id (FK), type (enum), input_data (JSON), modified_twin_data (JSON), original_risk_score, simulated_risk_score, risk_change, risk_category_before, risk_category_after, rag_explanation, rag_confidence, results (JSON), timestamps | What-if scenario results |
| `simulation_results` | id, user_id (FK), metabolic_score, insulin_score, sleep_score, stress_score, diet_score, pcos_risk, diabetes_risk, insulin_resistance_risk, timestamps | Aggregated outcome scores |
| `alerts` | id, user_id (FK), simulation_id (FK nullable), type (enum), title, message, severity (enum), is_read (bool), timestamps | Health warnings |

#### Dynamic Disease Catalog

| Table | Columns | Notes |
|-------|---------|-------|
| `diseases` | id, slug (unique), name, icon, description, is_active, sort_order, risk_weights (JSON), timestamps | Disease definitions (diabetes, pcod, etc.) |
| `disease_fields` | id, disease_id (FK), slug, label, field_type, category, options (JSON), validation (JSON), risk_config (JSON), sort_order, is_required, timestamps | Per-disease field specs |
| `user_disease_data` | id, user_id (FK), disease_id (FK), field_values (JSON), timestamps | User's disease-specific answers; unique on (user_id, disease_id) |

#### RAG Knowledge Base

| Table | Columns | Notes |
|-------|---------|-------|
| `rag_documents` | id, title, description, timestamps | Top-level document container |
| `rag_nodes` | id, document_id (FK), parent_id (FK nullable self-ref), title, summary, keywords (comma-separated string), depth (int), timestamps | Hierarchical tree nodes |
| `rag_pages` | id, node_id (FK), page_number, content (longText), timestamps | Content pages |
| `rag_query_logs` | id, user_id (FK), question, reasoning_path (JSON), selected_nodes (JSON), confidence, created_at | Audit trail for RAG queries |

---

## 6. Authentication & Authorization

### 6.1 Dual Authentication

| Channel | Method | Guard |
|---------|--------|-------|
| **API** | Sanctum Bearer Token | `auth:sanctum` |
| **Web** | Laravel Session + CSRF | `auth` (web guard) |

### 6.2 Authorization Middleware

| Middleware | Purpose |
|------------|---------|
| `guest` | Login/Register routes — blocks authenticated users |
| `auth` or `auth:sanctum` | Requires authenticated user |
| `admin` (`AdminMiddleware`) | Checks `user->is_admin === true`; returns 403 JSON (API) or abort 403 (Web) |

### 6.3 Rate Limiting

| Limiter | Rate | Applied To |
|---------|------|-----------|
| `api` | 60 req/min per user or IP | All API routes |
| `auth` | 10 req/min per IP | Login/Register (brute-force protection) |
| `rag` | 20 req/min per user or IP | RAG query endpoint (heavier computation) |

---

## 7. Risk Calculation Engine

The `RiskEngineService` is the mathematical core of the application. It computes 5 independent health scores and combines them into an overall risk.

### 7.1 Score Components

| Score | Range | Calculation Logic |
|-------|-------|-------------------|
| **Metabolic Health** | 0–100 | Base 50 + sleep penalty + stress penalty + activity penalty + water penalty + disease impact |
| **Insulin Resistance** | 0–100 | Base 30 + BMI factor (weight/height²) + sedentary penalty + disease impact |
| **Hormonal Imbalance** | 0–100 | Base 20 + stress factor + sleep factor + disease impact (PCOD-heavy) |
| **Sleep** | 0–100 | 7–9h → 100, 6h → 70, 5h → 45, <5h → 20 |
| **Stress** | 0–100 | LOW → 90, MEDIUM → 55, HIGH → 20 |
| **Diet** | 0–100 | Base 70 ± water intake adj ± sugar craving adj ± blood sugar adj |

### 7.2 Overall Risk Formula

```
overall_risk = (0.4 × metabolic) + (0.3 × insulin_resistance) + (0.3 × hormonal_imbalance)
```

### 7.3 Risk Categories

| Range | Category |
|-------|----------|
| 0–30 | LOW |
| 31–55 | MODERATE |
| 56–75 | HIGH |
| 76–100 | CRITICAL |

### 7.4 Disease Impact

Each disease's `risk_weights` JSON and each field's `risk_config` JSON define how user answers influence the three base scores (metabolic, insulin, hormonal). The engine evaluates rules per field and accumulates impact.

---

## 8. RAG Knowledge Base Architecture

The RAG system is a **keyword-based tree-traversal search engine** — not an LLM-based RAG. It operates entirely on structured data stored in the database.

### 8.1 Data Structure

```
RagDocument
  └── RagNode (root, depth=0)
       ├── RagNode (child, depth=1)
       │    ├── RagNode (leaf, depth=2)
       │    │    └── RagPage (content)
       │    └── RagNode (leaf, depth=2)
       │         └── RagPage (content)
       └── RagNode (child, depth=1)
            └── RagPage (content)
```

### 8.2 Search Pipeline

```
User Question
     │
     ▼
┌─────────────────┐
│  Tokenize       │  RagScoringService.tokenize()
│  (remove 70+    │  Lowercase, remove stopwords,
│   stopwords)    │  keep tokens > 2 chars
└────────┬────────┘
         ▼
┌─────────────────┐
│  Traverse Tree  │  RagTraversalEngine.traverse()
│  (greedy best-  │  Score root nodes → descend into
│   child descent)│  highest-scoring child → repeat
└────────┬────────┘
         ▼
┌─────────────────┐
│  Score Nodes    │  RagScoringService.scoreNode()
│  (keyword count │  Count keyword matches + disease
│   + context)    │  context bonus (+2)
└────────┬────────┘
         ▼
┌─────────────────┐
│  Build Answer   │  RagAnswerBuilder.build()
│  (fetch pages,  │  Concatenate terminal node pages,
│   truncate)     │  trim to 2000 chars
└────────┬────────┘
         ▼
┌─────────────────┐
│  Confidence     │  RagConfidenceService.calculate()
│  60 + 10×depth  │  + 5×matches, max 95
│  + 5×matches    │
└────────┬────────┘
         ▼
    Answer + Reasoning Path + Sources + Confidence
```

### 8.3 Components

| Component | Responsibility |
|-----------|---------------|
| `RagSearchService` | Orchestrator: tokenize → traverse → build → confidence → log |
| `RagTraversalEngine` | Tree traversal: score nodes at each depth, greedily descend |
| `RagScoringService` | Tokenization + keyword matching + disease context scoring |
| `RagAnswerBuilder` | Fetch page content from terminal nodes, format answer |
| `RagConfidenceService` | Calculate confidence score (0–95) from depth and match count |

### 8.4 Knowledge Domains (Seeded)

| Document | Root Topics |
|----------|-------------|
| **Diabetes Knowledge Base** | Blood Sugar Management, Insulin Resistance, Diet & Nutrition, Physical Activity, Complications |
| **PCOD Knowledge Base** | (5 root topics with ~30 child nodes) |
| **Lifestyle & Nutrition** | Sleep, Stress, Indian Nutrition, Hydration, Gut Health |

---

## 9. Simulation Engine

### 9.1 Simulation Types

| Type | Modifier Logic | Example |
|------|---------------|---------|
| `MEAL` | Adjusts sugar craving frequency in snapshot | "What if I eat healthier meals?" |
| `SLEEP` | Modifies `avg_sleep_hours` in snapshot | "What if I sleep 8 hours?" |
| `STRESS` | Changes `stress_level` in snapshot | "What if my stress is low?" |
| `FOOD_IMPACT` | Adjusts blood sugar based on food GI + builds alternatives | "What happens if I eat white rice?" |

### 9.2 Simulation Flow

```
User Input (type + parameters)
     │
     ▼
Retrieve Active Digital Twin (snapshot_data)
     │
     ▼
Apply Lifestyle Modifier → Modified Snapshot
     │
     ▼
Recalculate Risk (RiskEngineService.recalculateFromSnapshot)
     │
     ▼
Query RAG for Explanation (contextual to simulation)
     │
     ▼
Store Simulation Record (before/after comparison)
     │
     ▼
Evaluate Alert Rules (AlertService.evaluate)
     │
     ▼
Return Results + Alerts + RAG Explanation
```

---

## 10. Alert System

### 10.1 Alert Rules (evaluated after each simulation)

| Rule | Trigger | Severity |
|------|---------|----------|
| Risk Threshold | `simulated_risk_score > 75` | CRITICAL |
| High GI Food | Food item is on high-glycemic list | WARNING |
| Low Sleep | `sleep_hours < 6` | WARNING |
| High Stress | `stress_level == HIGH` | WARNING |
| Repeated Risk | 3+ high-risk simulations in 7 days | CRITICAL |

### 10.2 High-GI Food List (Hardcoded)

The `AlertService` checks food items against a hardcoded list of high-glycemic foods (e.g., white rice, white bread, sugary drinks).

---

## 11. Admin Subsystem

### 11.1 Dashboard Metrics

| Metric | Source |
|--------|--------|
| Total Users | `UserRepository.totalCount()` |
| New Users (7 days) | `UserRepository.newUsersCount(7)` |
| Simulations (today/week/total) | `SimulationRepository.todayCount()`, `.weekCount()`, `.totalCount()` |
| Average Risk Score | `DigitalTwinRepository.averageRiskScore()` |
| Risk Distribution | `DigitalTwinRepository.riskDistribution()` (counts per LOW/MODERATE/HIGH/CRITICAL) |
| Unread Alerts | `AlertRepository.totalUnreadCount()` |

### 11.2 Reports

The `ReportController` generates aggregate reports for a configurable period (default 30 days) including:
- New user registrations
- Simulation counts
- Risk distribution
- Daily risk score trends
- Daily simulation counts
- Daily alert counts by severity

### 11.3 RAG Management (CRUD)

Admins can fully manage the knowledge base:
- Create/edit/delete **Documents** (top-level containers)
- Create/edit/delete **Nodes** (hierarchical topics with keywords)
- Create/edit/delete **Pages** (content within nodes)
- Cascading deletes (deleting a node removes all children and pages)

---

## 12. Seeded Data

| Seeder | Purpose |
|--------|---------|
| `AdminUserSeeder` | Creates admin account (`admin@hormone.ai` / `admin123`) |
| `DiseaseSeeder` | Seeds Diabetes and PCOD disease definitions with ~11 fields each, including risk_config per field |
| `DiabetesRagSeeder` | Seeds Diabetes knowledge base (5 root topics, 30+ nodes with content pages) |
| `PcodRagSeeder` | Seeds PCOD knowledge base |
| `LifestyleNutritionRagSeeder` | Seeds general lifestyle knowledge (Sleep, Stress, Nutrition, Hydration, Gut Health) |
| `AdminDashboardSeeder` | Generates 30 demo users with profiles, twins, simulations, and alerts for dashboard testing |
| `DemoDataSeeder` | Creates 3 named demo users (Priya, Anita, Rahul) with specific disease profiles |
