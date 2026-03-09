# HormoneLens — Simulation Architecture

> **"See Your Health Before You Live It"**
>
> HormoneLens is an AI-powered **Hormonal Digital Twin** that shifts healthcare from **Reactive Tracking** to **Predictive Simulation**. Instead of logging past symptoms, it creates a virtual mirror of the user's metabolism, allowing users to simulate how a meal, sleep change, or stress level will impact their glucose and cortisol levels **before** they make the decision.

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Digital Twin Engine](#2-digital-twin-engine)
3. [Simulation Pipeline](#3-simulation-pipeline)
4. [Risk Calculation Engine](#4-risk-calculation-engine)
5. [Glucose Curve Prediction](#5-glucose-curve-prediction)
6. [RAG Knowledge Base](#6-rag-knowledge-base)
7. [AI / Bedrock Integration](#7-ai--bedrock-integration)
8. [Alert System](#8-alert-system)
9. [Data Flow Diagrams](#9-data-flow-diagrams)
10. [Missing Features & Gaps](#10-missing-features--gaps)
11. [Known Bugs & Issues](#11-known-bugs--issues)

---

## 1. System Overview

### Architecture Layers

```
┌─────────────────────────────────────────────────────────┐
│                   Frontend Dashboard                     │
│              (Chart.js, Live Simulation UI)               │
├─────────────────────────────────────────────────────────┤
│                  Laravel Orchestrator                     │
│   Controllers → Services → Repositories → Models         │
├──────────┬──────────┬──────────┬────────────────────────┤
│ Digital  │ Simulation│  Risk   │   Alert                 │
│ Twin     │ Engine    │ Engine  │   System                │
│ Service  │ Service   │ Service │   Service               │
├──────────┴──────────┴──────────┴────────────────────────┤
│              RAG Knowledge Base (Tree-Based)              │
│   RagSearchService → RagTraversalEngine → AnswerBuilder  │
├─────────────────────────────────────────────────────────┤
│            Amazon Bedrock (Claude 3.5 Sonnet)            │
│       Reasoning Engine + Guardrails + RAG Synthesis      │
├─────────────────────────────────────────────────────────┤
│                PostgreSQL + Redis Cache                   │
└─────────────────────────────────────────────────────────┘
```

### Key Components

| Component | Class | Responsibility |
|-----------|-------|----------------|
| Digital Twin | `DigitalTwinService` | Generates a virtual metabolic clone of the user |
| Simulation Engine | `SimulationService` | Orchestrates lifestyle & food simulations |
| Risk Engine | `RiskEngineService` | Calculates metabolic, insulin, hormonal, and overall risk scores |
| Glucose Curve | `GlucoseCurveService` | Predicts time-series glucose response to food |
| RAG Search | `RagSearchService` | Tree-based knowledge retrieval for personalized context |
| AI Service | `BedrockService` | Amazon Bedrock integration for reasoning & synthesis |
| Guardrails | `GuardrailService` | Input sanitization + response safety validation |
| Alerts | `AlertService` | Risk-based health alerts with AI enhancement |

---

## 2. Digital Twin Engine

The **Digital Twin** is the foundational data structure — a snapshot-in-time of the user's metabolic identity. Every simulation operates on a **cloned and modified** copy of this twin.

### Generation Flow

```
User Health Profile + Disease Data
        │
        ▼
┌─────────────────────────┐
│  DigitalTwinService     │
│  .generate(user)        │
│                         │
│  1. Load health profile │
│  2. Load disease data   │
│     (diabetes, pcod...) │
│  3. Build snapshot_data │
│  4. Calculate all       │
│     risk scores         │
│  5. Deactivate old twin │
│  6. Store new active    │
│     twin                │
└─────────────────────────┘
```

### Snapshot Data Structure

```json
{
  "health_profile": {
    "weight": 70,
    "height": 165,
    "avg_sleep_hours": 6.5,
    "stress_level": "high",
    "physical_activity": "sedentary",
    "eating_habits": "irregular meals",
    "water_intake": 1.5,
    "disease_type": "diabetes",
    "gender": "female"
  },
  "diabetes": {
    "avg_blood_sugar": 180,
    "sugar_cravings": "frequent",
    "hba1c": 7.5
  },
  "pcod": {
    "cycle_regularity": "irregular",
    "sugar_cravings": "occasional"
  }
}
```

### Scores Stored on Twin

| Score | Range | Description |
|-------|-------|-------------|
| `metabolic_health_score` | 0–100 | Higher = worse metabolic health |
| `insulin_resistance_score` | 0–100 | Higher = more insulin resistant |
| `sleep_score` | 0–100 | Higher = **better** sleep quality |
| `stress_score` | 0–100 | Higher = **lower** stress |
| `diet_score` | 0–100 | Higher = **better** diet quality |
| `overall_risk_score` | 0–100 | Weighted: metabolic 40%, insulin 30%, hormonal 30% |
| `risk_category` | Enum | LOW (≤30), MODERATE (≤55), HIGH (≤75), CRITICAL (>75) |

---

## 3. Simulation Pipeline

### Simulation Types

| Type | Enum Value | What It Simulates |
|------|-----------|-------------------|
| **Meal** | `meal` | Diet changes — reducing sugar, adding vegetables |
| **Sleep** | `sleep` | Sleep duration changes — e.g., simulate 5h vs 8h sleep |
| **Stress** | `stress` | Stress level changes — low/medium/high |
| **Food Impact** | `food_impact` | Specific food item → glucose curve + risk impact |

### Lifestyle Simulation Flow (meal / sleep / stress)

```
POST /api/simulations/run
  { "type": "sleep", "description": "...", "parameters": { "sleep_hours": 5 } }

        │
        ▼
┌─────────────────────────────────────────┐
│         SimulationService               │
│         .simulateLifestyleChange()      │
│                                         │
│  1. Load active Digital Twin            │
│  2. Clone snapshot_data                 │
│  3. applyLifestyleModifier()            │
│     ├── MEAL: modify sugar_cravings,    │
│     │         eating_habits             │
│     ├── SLEEP: modify avg_sleep_hours   │
│     └── STRESS: modify stress_level     │
│                                         │
│  4. RiskEngineService                   │
│     .recalculateFromSnapshot()          │
│     → new metabolic/insulin/hormonal    │
│       /overall scores                   │
│                                         │
│  5. RAG Search: retrieve context        │
│     matching user's condition           │
│                                         │
│  6. Bedrock AI: generate explanation    │
│     combining risk deltas + RAG context │
│                                         │
│  7. Store Simulation record             │
│                                         │
│  8. AlertService.evaluate()             │
│     → generate alerts if thresholds hit │
│                                         │
│  9. Return simulation + alerts          │
└─────────────────────────────────────────┘
```

### Food Impact Simulation Flow

```
POST /api/food-impact
  { "food_item": "white rice", "quantity": "1 cup", "meal_time": "evening" }

        │
        ▼
┌──────────────────────────────────────────────┐
│         SimulationService                     │
│         .simulateFoodImpact()                 │
│                                               │
│  1. Load active Digital Twin                  │
│                                               │
│  2. GlucoseCurveService.predict()             │
│     ├── Lookup FoodGlycemicData table         │
│     ├── Fallback: estimate for unknown food   │
│     ├── Compute cross-factor modifiers        │
│     │   (sleep × stress × activity × timing)  │
│     ├── Adjust spike, peak time, recovery     │
│     └── Build time-series glucose curve       │
│                                               │
│  3. RAG Search: "impact of {food} on          │
│     {disease} blood sugar insulin hormones"   │
│                                               │
│  4. applyFoodModifier()                       │
│     ├── Classify food (high/low GI)           │
│     ├── Modify avg_blood_sugar in snapshot    │
│     └── Modify sugar_cravings                 │
│                                               │
│  5. Recalculate risk scores                   │
│                                               │
│  6. Build alternatives                        │
│     ├── AI-generated (Bedrock)                │
│     ├── Database alternatives (FoodGlycemicData)│
│     └── Hardcoded fallback                    │
│                                               │
│  7. Store simulation with:                    │
│     glucose_curve, peak, recovery,            │
│     food_data, modifiers, alternatives        │
│                                               │
│  8. Evaluate alerts                           │
│  9. Return simulation + alerts                │
└──────────────────────────────────────────────┘
```

---

## 4. Risk Calculation Engine

### Score Calculations

#### Metabolic Risk (0–100, higher = worse)

| Factor | Condition | Impact |
|--------|-----------|--------|
| Sleep | < 6 hours | +15 |
| Sleep | < 7 hours | +5 |
| Stress | High | +20 |
| Stress | Medium | +10 |
| Activity | Sedentary | +15 |
| Activity | Moderate | +5 |
| Water | < 2L/day | +5 |
| Disease fields | Dynamic via `risk_config` | Variable |

Base score: **50**

#### Insulin Resistance (0–100, higher = worse)

| Factor | Condition | Impact |
|--------|-----------|--------|
| BMI | > 30 (obese) | +25 |
| BMI | 25–30 (overweight) | +15 |
| Activity | Sedentary | +10 |
| Disease fields | Dynamic via `risk_config` | Variable |

Base score: **30**

#### Hormonal Imbalance (0–100, higher = worse)

| Factor | Condition | Impact |
|--------|-----------|--------|
| Stress | High | +15 |
| Sleep | < 6 hours | +10 |
| Disease fields | Dynamic via `risk_config` | Variable |

Base score: **20**

#### Overall Risk

```
overall = (metabolic × 0.4) + (insulin × 0.3) + (hormonal × 0.3)
```

#### Risk Categories

| Category | Score Range |
|----------|------------|
| LOW | 0–30 |
| MODERATE | 31–55 |
| HIGH | 56–75 |
| CRITICAL | 76–100 |

### Dynamic Disease Impact

Disease-specific risk contributions are **not hardcoded** — they're driven by `disease_fields.risk_config`:

```json
{
  "score": "metabolic",
  "rules": [
    { "operator": ">", "value": 200, "impact": 15 },
    { "operator": ">", "value": 140, "impact": 8 }
  ]
}
```

The engine iterates over all disease data in the snapshot, loads matching `DiseaseField` records, and sums impacts. This supports any future disease (thyroid, etc.) without code changes.

---

## 5. Glucose Curve Prediction

### Mathematical Model

The `GlucoseCurveService` uses an **asymmetric Gaussian model**:

**Rise Phase** (0 → peak time):  
$$glucose(t) = baseline + spike \times \left(1 - (1 - \frac{t}{t_{peak}})^2\right)$$

**Decay Phase** (peak → recovery):  
$$glucose(t) = baseline + spike \times e^{-3 \times \frac{t - t_{peak}}{t_{recovery}}}$$

### Cross-Factor Modifiers

Every glucose prediction is influenced by the user's current metabolic state:

| Factor | Good State | Bad State | Range |
|--------|-----------|-----------|-------|
| Sleep | ≥8h → ×0.95 | <5h → ×1.40 | 0.95–1.40 |
| Stress | Low → ×0.90 | High → ×1.20 | 0.90–1.20 |
| Activity | Very active → ×0.75 | Sedentary → ×1.15 | 0.75–1.15 |
| Meal time | Morning → ×0.85 | Night → ×1.35 | 0.85–1.35 |

**Combined modifier** = sleep × stress × activity × meal_time

**Worst case** (poor sleep, high stress, sedentary, late night):  
`1.40 × 1.20 × 1.15 × 1.35 = 2.61× spike amplification`

### Data Sources

1. **Primary**: `food_glycemic_data` table — curated GI, GL, spike, peak time, recovery, alternatives
2. **Fallback**: Conservative estimates for unknown foods (GI=55, spike=30mg/dL)

### Output

```json
{
  "curve": [
    { "time_minutes": 0, "glucose_mg_dl": 100.0 },
    { "time_minutes": 15, "glucose_mg_dl": 118.5 },
    { "time_minutes": 30, "glucose_mg_dl": 145.2 },
    { "time_minutes": 45, "glucose_mg_dl": 158.7 },
    { "time_minutes": 60, "glucose_mg_dl": 142.3 },
    { "time_minutes": 90, "glucose_mg_dl": 112.0 },
    { "time_minutes": 120, "glucose_mg_dl": 102.5 }
  ],
  "peak": { "glucose_mg_dl": 158.7, "time_minutes": 45 },
  "recovery_minutes": 90,
  "baseline_mg_dl": 100.0,
  "modifiers": {
    "sleep": { "value": 5.5, "factor": 1.30 },
    "stress": { "value": "high", "factor": 1.20 },
    "activity": { "value": "sedentary", "factor": 1.15 },
    "meal_time": { "value": "evening", "factor": 1.15 },
    "combined": 2.07
  }
}
```

---

## 6. RAG Knowledge Base

### Architecture: Tree-Based Retrieval

Unlike vector-embedding RAG systems, HormoneLens uses a **hierarchical keyword-scored tree traversal** model:

```
RagDocuments
  └── RagNodes (tree structure via parent_id)
       ├── Root Node: "Diabetes Management"
       │    ├── Child: "Blood Sugar Control"
       │    │    └── Child: "Post-Meal Glucose"
       │    └── Child: "Insulin Resistance"
       └── Root Node: "PCOS/PCOD"
            ├── Child: "Hormonal Imbalance"
            └── Child: "Cycle Management"

RagPages (content attached to leaf/terminal nodes)
```

### Query Flow

```
User Question: "How does rice affect my diabetes blood sugar?"
                           │
                           ▼
┌────────────────────────────────────────────┐
│  1. TOKENIZE                               │
│     Remove stopwords → ["rice", "affect",  │
│     "diabetes", "blood", "sugar"]          │
│                                            │
│  2. SCORE ROOT NODES                       │
│     ├── "Diabetes Management" → score: 4   │
│     ├── "PCOS/PCOD" → score: 0            │
│     └── Best root: "Diabetes Management"   │
│                                            │
│  3. TRAVERSE DOWN                          │
│     ├── "Blood Sugar Control" → score: 3   │
│     ├── "Insulin Resistance" → score: 1    │
│     └── Best child → traverse deeper       │
│                                            │
│  4. STOP when no child scores higher       │
│     Terminal nodes = last 1–2 in path      │
│                                            │
│  5. BUILD ANSWER                           │
│     Pull RagPages from terminal nodes      │
│     ├── If AI enabled: synthesize via      │
│     │   Bedrock with ragSynthesis prompt   │
│     └── If AI disabled: concatenate pages  │
│                                            │
│  6. CALCULATE CONFIDENCE                   │
│     60 + (10 × depth) + (5 × matches)     │
│     Max: 95                                │
│                                            │
│  7. LOG to RagQueryLog with AI metadata    │
└────────────────────────────────────────────┘
```

### Scoring Algorithm

```
scoreNode(nodeKeywords, queryTokens, diseaseContext):
  score = 0
  for each token in queryTokens:
    for each keyword in nodeKeywords:
      if keyword contains token OR token contains keyword:
        score++
  if diseaseContext in nodeKeywords:
    score += 2  (disease context bonus)
  return score
```

### Confidence Formula

```
confidence = 60 + (10 × depthReached) + (5 × totalKeywordMatches)
clamped to [0, 95]
```

### RAG Integration Points

| Integration Point | How RAG is Used |
|-------------------|-----------------|
| Lifestyle Simulation | Query with user's description + disease context for explanation |
| Food Impact Simulation | Query "impact of {food} on {disease} blood sugar insulin hormones" |
| RAG Query Endpoint | Direct user questions → tree traversal → AI synthesis |
| Streaming Endpoint | SSE stream for real-time RAG answers |

---

## 7. AI / Bedrock Integration

### Service Architecture

```
┌──────────────────────────────┐
│      BedrockService          │
│  ┌────────────────────────┐  │
│  │  GuardrailService      │  │
│  │  • sanitizeInput()     │  │
│  │  • validateResponse()  │  │
│  │  • isDiagnosisRequest()│  │
│  └────────────────────────┘  │
│                              │
│  Methods:                    │
│  • ask()         → invoke    │
│  • stream()      → SSE       │
│  • conversation() → multi-turn│
│  • fallback to 'fast' model  │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Amazon Bedrock              │
│  • Default model: Claude 3.5 │
│  • Fast model: alias 'fast'  │
│  • Config stored in DB       │
│    (ai_settings table)       │
└──────────────────────────────┘
```

### Prompt Templates

| Template | Used By | Purpose |
|----------|---------|---------|
| `ragSynthesis()` | RagAnswerBuilder | Synthesize knowledge base excerpts into clear answer |
| `simulationExplanation()` | SimulationService | Explain risk score changes from lifestyle simulation |
| `foodImpact()` | SimulationService | Analyze food's nutritional/metabolic impact |
| `alertContext()` | AlertService | Enhance alert messages with contextual advice |
| `riskNarrative()` | (Available) | Summarize user's risk profile |

### Feature Flags (in `ai_settings` table)

| Key | Default | Controls |
|-----|---------|----------|
| `simulation_ai_explanation` | `true` | Whether simulations get AI-generated explanations |
| `rag_ai_synthesis` | `true` | Whether RAG answers are synthesized by AI |
| `alert_ai_enhancement` | `true` | Whether alerts get AI-enhanced messages |

### Guardrails

| Layer | Action |
|-------|--------|
| **Input sanitization** | Truncate to 2000 chars, strip prompt injection markers (`SYSTEM:`, `ASSISTANT:`, `HUMAN:`) |
| **Response validation** | Append medical disclaimer when response contains action words (`recommend`, `should`, `try`, etc.) |
| **Diagnosis detection** | Detect requests for prescriptions, diagnoses, or medications |
| **Fallback strategy** | If primary model rate-limited → retry with 'fast' model; if cost exceeded → graceful error |

---

## 8. Alert System

### Alert Evaluation Conditions

The `AlertService.evaluate()` runs after every simulation and checks 5 conditions:

| # | Alert Type | Condition | Severity |
|---|-----------|-----------|----------|
| 1 | `RISK_THRESHOLD` | Simulated risk score > 75 | CRITICAL |
| 2 | `HIGH_GI` | Food item matches high-GI food list or RAG mentions "glucose spike" | WARNING |
| 3 | `LOW_SLEEP` | Sleep hours < 6 (from simulation input) | WARNING |
| 4 | `HIGH_STRESS` | Stress level set to "high" in modified snapshot | WARNING |
| 5 | `REPEATED_RISK` | 3+ high-risk simulations in past 7 days | CRITICAL |

### AI Enhancement

Each alert message is optionally enhanced via Bedrock's 'fast' model using the `alertContext` prompt template — adding actionable, personalized recommendations while preserving the original meaning.

---

## 9. Data Flow Diagrams

### End-to-End Simulation Flow

```
User Input (meal/sleep/stress/food)
       │
       ▼
┌─── SimulationController ───┐
│   Validate request          │
│   Route to SimulationService│
└─────────────┬───────────────┘
              │
              ▼
┌─── Digital Twin ───────────┐
│   Load active twin          │
│   Clone snapshot_data       │
└─────────────┬───────────────┘
              │
     ┌────────┴────────┐
     ▼                 ▼
┌─ Modify ─┐   ┌─ Glucose Curve ─┐
│ snapshot  │   │ (food_impact    │
│ based on  │   │  only)          │
│ sim type  │   │ predict()       │
└────┬──────┘   └───────┬─────────┘
     │                  │
     └────────┬─────────┘
              │
              ▼
┌─── Risk Engine ────────────┐
│   recalculateFromSnapshot() │
│   → metabolic, insulin,     │
│     hormonal, overall       │
│   → risk_category           │
└─────────────┬───────────────┘
              │
     ┌────────┴────────┐
     ▼                 ▼
┌─ RAG Search ─┐  ┌─ AI Explain ─┐
│ tokenize →   │  │ Bedrock ask()│
│ traverse →   │  │ with prompt  │
│ answer →     │  │ template     │
│ confidence   │  │              │
└──────┬───────┘  └──────┬───────┘
       │                 │
       └────────┬────────┘
                │
                ▼
    ┌─── Store Simulation ───┐
    │   input, modified data, │
    │   risk scores, RAG      │
    │   explanation, results  │
    └─────────┬───────────────┘
              │
              ▼
    ┌─── Alert Evaluation ───┐
    │   5 conditions checked  │
    │   AI-enhanced messages  │
    └─────────┬───────────────┘
              │
              ▼
        Response to User
   (simulation + alerts JSON)
```

### RAG + AI Synthesis Flow

```
User Question / Simulation Context
              │
              ▼
┌─── RagSearchService.search() ──────────────┐
│                                             │
│  ┌─ RagScoringService.tokenize() ────┐     │
│  │  Input: "How does rice affect      │     │
│  │         my diabetes?"              │     │
│  │  Output: ["rice","affect",         │     │
│  │           "diabetes"]              │     │
│  └─────────────────────┬──────────────┘     │
│                        │                    │
│  ┌─ RagTraversalEngine.traverse() ──────┐  │
│  │  Score root nodes by keyword match    │  │
│  │  Select best root → traverse children │  │
│  │  Stop when no child improves score    │  │
│  │  Output: path + terminal nodes        │  │
│  └──────────────────────┬────────────────┘  │
│                         │                   │
│  ┌─ RagAnswerBuilder.build() ────────────┐  │
│  │  Pull RagPages from terminal nodes     │  │
│  │  Concatenate page content              │  │
│  │  IF rag_ai_synthesis enabled:          │  │
│  │    → BedrockService.ask()              │  │
│  │      with ragSynthesis prompt          │  │
│  │  ELSE:                                 │  │
│  │    → Truncated concatenation           │  │
│  └──────────────────────┬─────────────────┘  │
│                         │                   │
│  ┌─ RagConfidenceService.calculate() ────┐  │
│  │  60 + (10 × depth) + (5 × matches)    │  │
│  └────────────────────────────────────────┘  │
└─────────────────────────────────────────────┘
```

---

## 10. Missing Features & Gaps

### 10.1 Simulation Gaps vs. Vision

These features were described in the submission documents but are currently **not implemented** in the codebase:

| # | Feature | Vision (from Submissions) | Current Status | Priority |
|---|---------|--------------------------|----------------|----------|
| **S1** | **Thyroid Simulation Support** | Submissions list Thyroid as a third supported condition (TSH instability, metabolic slowdown, weight gain propensity, fatigue risk, hypo/hyperthyroid progression) | No thyroid-specific simulation logic exists. The dynamic disease system supports adding thyroid via DB, but no thyroid-specific modifiers, fields, or RAG documents exist | HIGH |
| **S2** | **Cortisol Level Prediction** | "Cortisol Imbalance Detection" for both PCOS & Diabetic users | No cortisol modeling exists — stress is captured as a categorical enum (low/medium/high) but never translated into cortisol level predictions or numeric cortisol values | HIGH |
| **S3** | **Androgen Imbalance Prediction** | "Predict Androgen Imbalance" for PCOS users | No androgen-related calculations exist. Hormonal imbalance score is a generic number, not tied to specific hormones (testosterone, DHEA-S, etc.) | HIGH |
| **S4** | **Ovulation Stability / Cycle Prediction** | "Ovulation Stability Risk" and "Cycle Delay Risk Forecast" for PCOS users | `CycleRegularity` enum exists (regular/irregular/missed) but is only stored — no predictive model simulates how lifestyle changes affect cycle timing or ovulation | HIGH |
| **S5** | **HbA1c Trend Forecasting** | "HbA1c Trend Forecast" for Diabetic users | HbA1c may exist as a disease field, but no longitudinal trend calculation or forecasting model exists — each simulation is stateless | MEDIUM |
| **S6** | **Long-Term Outcome Prediction** | "PCOS Severity Progression", "Diabetes Complication Risk", "Fertility Health Risk" | Only immediate risk score changes are calculated. No multi-day/multi-week projection or disease progression modeling | HIGH |
| **S7** | **Period Regularity Prediction** | "Period Regularity Prediction" tied to physical activity | No menstrual cycle prediction engine exists | MEDIUM |
| **S8** | **Glycemic Load Sensitivity** | "Glycemic Load Sensitivity" as a simulation output | GL is stored in `food_glycemic_data` but not used as a distinct simulation output or personalized sensitivity metric | LOW |
| **S9** | **Physical Activity Simulation Type** | Activity variation is listed as a simulation dimension in submissions | No `ACTIVITY` simulation type exists. Activity is only a cross-factor modifier in glucose curve, not a standalone simulatable parameter | MEDIUM |
| **S10** | **Counterfactual "What-If" Comparison** | "If I eat pizza now vs. tomorrow morning, how does my risk change?" — comparative scenarios | `FoodCompareController` exists but only compares two foods at the same time. No temporal comparison (same food at different times/states) | MEDIUM |

### 10.2 RAG System Gaps

| # | Gap | Details |
|---|-----|---------|
| **R1** | **No Vector Embeddings** | Submissions reference ChromaDB for vector search. Current RAG uses keyword-based tree scoring, not semantic embeddings. This means semantically similar but lexically different queries may miss relevant nodes (e.g., "glucose" won't match a node with keyword "blood sugar" unless substring matching catches it) |
| **R2** | **No User History in RAG** | Vision states "retrieves your specific historical data (e.g., 'You spike on rice, but not on wheat')." Current RAG only searches the knowledge base — it doesn't incorporate user's personal simulation history into retrieval context |
| **R3** | **No Bedrock Knowledge Base Integration** | Submissions mention Amazon Bedrock Knowledge Bases as the RAG layer. Current implementation is a custom tree-based RAG, not using Bedrock's managed knowledge base service |
| **R4** | **Shallow Confidence Formula** | Confidence is calculated as `60 + (10 × depth) + (5 × matches)`, always starting at 60% minimum. This doesn't reflect actual answer quality — a one-token match at depth 1 yields 75% confidence which may be misleading |
| **R5** | **No RAG Document Ingestion Pipeline** | No automated pipeline to ingest/parse medical PDF documents into the RagDocument → RagNode → RagPage hierarchy. Documents must be manually structured |

### 10.3 AI Integration Gaps

| # | Gap | Details |
|---|-----|---------|
| **A1** | **No Multi-Turn Simulation Conversation** | `BedrockService.conversation()` exists but is never used. Users can't have follow-up questions about their simulation results |
| **A2** | **No Bedrock Guardrails (AWS Native)** | Submissions mention "Amazon Bedrock Guardrails" as a dedicated safety layer. Current guardrails are a simple PHP service (regex-based), not AWS's managed guardrail service |
| **A3** | **Limited Prompt Injection Protection** | `GuardrailService.sanitizeInput()` only strips `SYSTEM:`, `ASSISTANT:`, `HUMAN:` markers. Modern prompt injection techniques are not addressed |
| **A4** | **No Embedding-Based Food Recognition** | Unknown foods get a generic fallback (GI=55, spike=30). No AI-based food recognition or GI estimation from Bedrock |

### 10.4 Alert System Gaps

| # | Gap | Details |
|---|-----|---------|
| **AL1** | **No Real-Time Push Alerts** | Submissions mention Laravel Reverb for "live risk alerts to the dashboard". No WebSocket/broadcasting implementation exists |
| **AL2** | **No Alert Learning / Personalization** | Alerts use static thresholds (risk > 75, sleep < 6h). No adaptation based on user's baseline or historical trend |
| **AL3** | **Duplicate High-GI Food Lists** | The high-GI food list is hardcoded in both `AlertService` and `SimulationService.applyFoodModifier()` — should reference `food_glycemic_data` table or a shared constant |

### 10.5 Architecture Gaps

| # | Gap | Details |
|---|-----|---------|
| **AR1** | **No Redis Caching** | Submissions list Redis as a cache layer for "frequent simulation requests". No caching is implemented — every simulation recalculates from scratch |
| **AR2** | **No Chart.js / Visualization Backend** | Submissions reference Chart.js for predicted glucose spike graphs. No server-side preparation of chart data beyond the raw glucose curve array |
| **AR3** | **Stateless Simulations** | Each simulation is independent. No concept of "simulation sessions" where a user can chain multiple what-if scenarios (e.g., "What if I change diet AND sleep?") |
| **AR4** | **No Simulation Comparison Dashboard** | No endpoint to compare multiple past simulations side-by-side to visualize progress or different scenarios |

---

## 11. Known Bugs & Issues

### 11.1 Code-Level Bugs

| # | Bug | Location | Description | Severity |
|---|-----|----------|-------------|----------|
| **B1** | **Meal simulation modifiers are keyword-only** | `SimulationService::applyLifestyleModifier()` | Meal type simulation uses `str_contains()` on the description to detect changes (e.g., "reduce sugar", "more vegetables"). Any meal description that doesn't contain these exact phrases has **zero effect** on the snapshot. There's no AI-based parsing of the meal input | HIGH |
| **B2** | **Food modifier falls back to hardcoded lists** | `SimulationService::applyFoodModifier()` | When `GlucoseCurveService` returns data, GI-based classification works. But the fallback path duplicates a hardcoded high/low GI food list, creating maintenance burden and potential inconsistency with the database | MEDIUM |
| **B3** | **AI explanation called even when disabled** | `SimulationService::generateAIExplanation()` | When `simulation_ai_explanation` is disabled, the method calls `$this->bedrock->ask('', '')` which makes an actual (empty) API call instead of early-returning a failure result | LOW |
| **B4** | **Traversal fallback on zero-score root** | `RagTraversalEngine::traverse()` | When no root node scores > 0, the code falls back to the first root node anyway (`$best = $scored->first()` with score 0) and traverses its children. This can return irrelevant content with misleadingly high confidence | MEDIUM |
| **B5** | **Inconsistent score semantics** | `RiskEngineService` | Sleep score and stress score are "higher = better" (inverted scale), while metabolic/insulin/hormonal are "higher = worse". This is confusing and the `overall_risk_score` only uses metabolic/insulin/hormonal — sleep and stress scores influence risk indirectly through the other three but aren't included in the weighted average | LOW |
| **B6** | **Snapshot creates new HealthProfile from array** | `RiskEngineService::recalculateFromSnapshot()` | `new HealthProfile($data['health_profile'])` creates a model instance without actually querying the database. This works for attribute access but bypasses accessors/mutators if any exist | LOW |
| **B7** | **Disease DB queries inside risk loop** | `RiskEngineService::computeDiseaseImpact()` | For each disease slug in the snapshot, the engine queries `Disease` and `DiseaseField` from the database. In a simulation with 3 diseases, this is 6 extra queries per score calculation (18 total across metabolic/insulin/hormonal). Should be cached or preloaded | MEDIUM |
| **B8** | **No quantity factor in food simulation** | `SimulationService::simulateFoodImpact()` | The `quantity` field is accepted in the input but **never used** in the glucose curve calculation or risk modification. 1 cup vs 3 cups of rice produce identical results | HIGH |
| **B9** | **Alert stress check logic flaw** | `AlertService::evaluate()` | The stress alert checks both `$inputData['parameters']['stress_level']` AND `$simulationResult['type'] === 'stress'`, but the second condition triggers for ANY stress-type simulation, even if the simulated stress level is 'low' — it then checks `modified_twin_data` which would be 'low', correctly not firing, but the conditional chain is convoluted | LOW |
| **B10** | **Missing food_compare simulation storage** | `FoodCompareController` | Food comparison likely returns comparison data but may not store results as a Simulation record, breaking the history/rerun pattern | LOW |

### 11.2 Data Issues

| # | Issue | Description |
|---|-------|-------------|
| **D1** | **Empty RAG knowledge base** | If no `RagDocument`/`RagNode`/`RagPage` records exist, every simulation's RAG explanation will be "No relevant information found" — the system works but provides no personalized knowledge context |
| **D2** | **Limited food_glycemic_data** | Unknown foods fall back to generic estimates (GI=55). The database needs seeding with a comprehensive food list, especially Indian foods referenced in the submissions (jalebi, gulab jamun, roti variants, etc.) |
| **D3** | **No thyroid disease seed data** | While the dynamic disease system supports thyroid, no Disease/DiseaseField records exist for thyroid conditions |

---

## API Endpoints Reference

### Simulation Endpoints

```
POST   /api/simulations/run       → Run lifestyle simulation (meal/sleep/stress)
GET    /api/simulations            → List user's simulations
GET    /api/simulations/{id}       → Get simulation details
POST   /api/food-impact            → Run food impact simulation
POST   /api/food-compare           → Compare two foods side-by-side
```

### Digital Twin Endpoints

```
POST   /api/digital-twin/generate  → Generate new twin from current health data
GET    /api/digital-twin/active    → Get active twin
GET    /api/digital-twin/{id}      → Get specific twin
```

### RAG Endpoints

```
POST   /api/rag/query              → Query knowledge base (rate-limited)
POST   /api/rag/query-stream       → Stream RAG answer via SSE
```

### Alert Endpoints

```
GET    /api/alerts                 → List alerts (paginated)
PATCH  /api/alerts/{id}/read       → Mark alert as read
PATCH  /api/alerts/read-all        → Mark all alerts as read
```

### Supporting Endpoints

```
GET    /api/health-profile         → Get health profile
POST   /api/health-profile         → Create health profile
PUT    /api/health-profile         → Update health profile
GET    /api/diseases               → List all active diseases
GET    /api/diseases/{slug}        → Get disease details + user data
POST   /api/diseases/{slug}        → Save user disease data
GET    /api/history                → Simulation history
POST   /api/history/{id}/rerun     → Re-run a past simulation
DELETE /api/history/{id}           → Delete simulation
```
