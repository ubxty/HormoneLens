# HormoneLens — Complete Bedrock Testing Guide

> From registration to AI-powered responses, step by step.

---

## Prerequisites

1. **Server running**: `php artisan serve` → `http://127.0.0.1:8000`
2. **Database seeded**: `php artisan migrate:fresh --seed`
3. **Bedrock configured**: Admin panel → AI Dashboard → Bearer token saved & tested green
4. **Vite running** (for React components): `npm run dev`

---

## Architecture Overview — Where Bedrock Is Used

| Feature | Bedrock Called? | What It Does |
|---------|:-:|---|
| Registration & Login | ❌ | Pure auth, no AI |
| Health Profile | ❌ | Form data stored to DB |
| Disease Data (diabetes/PCOD) | ❌ | Form data stored to DB |
| Digital Twin Generation | ❌ | Pure math risk calculation |
| **Simulation (Lifestyle)** | ✅ | AI explains risk changes + generates alerts |
| **Food Impact Analysis** | ✅ | AI analyzes food effect + suggests alternatives |
| Food Comparison | ❌ | Pure glucose curve math (no AI) |
| **RAG Knowledge Query** | ✅ | AI synthesizes answers from medical knowledge base |
| **Alert Enhancement** | ✅ | AI enriches alert messages with actionable advice |
| Dashboard | ❌ | Reads existing twin data, charts only |

**4 Bedrock-powered features** — Simulations, Food Impact, RAG Query, Alert Enhancement.

---

## Complete Flow: Step-by-Step

### PHASE 0 — Admin Setup (One-Time)

1. Go to `http://127.0.0.1:8000/admin/login`
2. Login: `admin@hormonelens.com` / `admin123`
3. Navigate to **AI Dashboard** (`/admin/bedrock`)
4. Under **AWS Credentials**:
   - Select **Bearer Token** tab
   - Paste your `ABSK...` token
   - Select region (e.g., `us-east-1`)
   - Click **💾 Save Credentials**
5. Click **🔌 Test Connection** → should show green "✓ Connection Successful"
6. Under **Model Configuration**:
   - Set **Default Model** to `anthropic.claude-3-5-sonnet-20241022-v2:0`
   - Set **Fast Model** to `anthropic.claude-3-haiku-20240307-v1:0`
   - Click **💾 Save**
7. Click **🧠 View Models** to confirm models are loading

---

### PHASE 1 — User Registration

**Page**: `http://127.0.0.1:8000/register`

**Fields to fill**:
| Field | Example Value |
|-------|-------------|
| Full Name | Priya Sharma |
| Email | priya@example.com |
| Password | Password123! |
| Confirm Password | Password123! |

**What happens**: User is created in `users` table → redirected to `/onboarding`

**Bedrock used?** No

---

### PHASE 2 — Onboarding (React App)

**Page**: `http://127.0.0.1:8000/onboarding`

This is a multi-step React wizard. Follow the on-screen steps to complete initial profile setup. After completion → redirected to `/dashboard`.

**Bedrock used?** No

---

### PHASE 3 — Health Profile

**Page**: `http://127.0.0.1:8000/health-profile`

**Fields to fill**:
| Field | Example (PCOS User) | Example (Diabetes User) |
|-------|---------------------|------------------------|
| Weight (kg) | 68 | 85 |
| Height (cm) | 162 | 170 |
| Avg. Sleep (hours) | 6 | 5.5 |
| Water Intake (litres) | 1.5 | 2 |
| Stress Level | High | Medium |
| Physical Activity | Sedentary | Moderate |
| Primary Condition | PCOD/PCOS | Type-2 Diabetes |
| Eating Habits | Irregular meals, late dinner | High carb diet |

Click **Save** → profile stored via `POST /api/health-profile`

**Bedrock used?** No — stored in `health_profiles` table.

---

### PHASE 4 — Disease-Specific Data

**Page**: `http://127.0.0.1:8000/disease/diabetes` or `/disease/pcod`

After saving health profile, the user should fill in disease-specific data. The available diseases are:

#### For Diabetes (`/disease/diabetes`):
| Field | Example Value |
|-------|-------------|
| Fasting Blood Sugar (mg/dL) | 140 |
| Post-meal Blood Sugar (mg/dL) | 210 |
| HbA1c (%) | 7.2 |
| Years Since Diagnosis | 3 |
| Family History | Yes |
| Frequent Urination | Yes |
| Excessive Thirst | Yes |
| Blurred Vision | No |
| Numbness/Tingling | No |

#### For PCOD (`/disease/pcod`):
| Field | Example Value |
|-------|-------------|
| Cycle Length (days) | 45 |
| Cycle Regularity | Irregular |
| Missed Periods (last 6 months) | 3 |
| Acne Severity | Moderate |
| Hair Thinning | Yes |
| Excess Body Hair | Mild |
| Weight Gain Pattern | Central/Abdominal |
| Family History PCOD | Yes |
| Diagnosed by Doctor | Yes |

Click **Save** → data stored via `POST /api/diseases/{slug}`

**Bedrock used?** No

---

### PHASE 5 — Generate Digital Twin ⭐

**Page**: `http://127.0.0.1:8000/digital-twin`

1. Click **🧬 Generate Digital Twin**
2. System calls `POST /api/digital-twin/generate`
3. **RiskEngineService** calculates 6 scores from your health + disease data:
   - **Metabolic Risk** (0-100) — based on sleep, stress, activity, disease fields
   - **Insulin Resistance** (0-100) — based on BMI, activity, blood sugar
   - **Hormonal Imbalance** (0-100) — based on stress, sleep, cycle data
   - **Sleep Score** (0-100, higher = better)
   - **Stress Score** (0-100, higher = better)
   - **Diet Score** (0-100) — based on water, cravings, blood sugar
4. Overall Risk = weighted average (Metabolic 40%, Insulin 30%, Hormonal 30%)

**What you see**:
- Interactive body silhouette with glowing risk zones
- Risk ring showing overall score (Low/Medium/High/Critical)
- Score cards for each metric
- History table of all twin snapshots

**Bedrock used?** No — pure mathematical calculations

**⚠️ IMPORTANT**: A Digital Twin MUST exist before simulations or food impact can work. These features operate on the twin's snapshot.

---

### PHASE 6 — Run Simulation 🤖 **[BEDROCK REQUIRED]**

**Page**: `http://127.0.0.1:8000/simulations`

This is the core "What-If" engine. Three simulation types:

#### Story A: Meal Simulation
1. Select **Simulation Type**: `Meal`
2. Pick a favorite food: `Biryani` (or type custom)
3. Select meal timing: `Late night`
4. Add optional description
5. Click **▶ Run Simulation**

**What happens behind the scenes**:
```
POST /api/simulations/run
  → SimulationService::simulateLifestyleChange()
    → Gets your active Digital Twin snapshot
    → Applies meal modifiers (stress + sleep affect insulin sensitivity)
    → Recalculates risk scores with modified data
    → ✅ BEDROCK CALL #1: generateAIExplanation()
      System prompt: "You are a metabolic health analyst..."
      User message: Contains simulation type, changes, risk delta, RAG context
      Response: Natural language explanation of what happened to your body
    → ✅ BEDROCK CALL #2: AlertService::evaluate()
      System prompt: "You are a health alert generator..."
      Response: Enhanced alert messages with actionable advice
    → Returns complete simulation result
```

**What you see**:
- **Risk Score Change**: e.g., "Overall risk: 42 → 58 (+16)" with red/green indicators
- **Category Change**: e.g., "Medium → High"
- **AI Explanation** (from Bedrock): "Eating Biryani late at night significantly impacts your metabolic health because your high stress level (High) has already reduced insulin sensitivity. Combined with only 6 hours of sleep..."
- **Predicted Alerts**: e.g., "⚠️ Your post-meal glucose is likely to spike above 200 mg/dL. Consider a lighter evening meal."
- **3D character** animation reacting to risk level

#### Story B: Sleep Change Simulation
1. Select **Simulation Type**: `Sleep`
2. Set sleep hours: `4` (simulating poor sleep)
3. Click **▶ Run Simulation**

**Expected AI response**: Explanation of how reduced sleep affects cortisol, insulin resistance, and metabolic risk for your specific profile.

#### Story C: Stress Level Simulation
1. Select **Simulation Type**: `Stress`
2. Set stress level: `High`
3. Click **▶ Run Simulation**

**Expected AI response**: Explanation of how elevated stress triggers cortisol imbalance, affecting glucose metabolism and hormonal balance.

---

### PHASE 7 — Food Impact Analysis 🤖 **[BEDROCK REQUIRED]**

**Page**: `http://127.0.0.1:8000/food-impact`

#### Story D: Analyze a Single Food
1. Type food item: `White Rice` (or use quick-pick buttons)
2. Set quantity: `200g`
3. Select meal time: `Lunch`
4. Click **🔍 Analyze Impact**

**What happens**:
```
POST /api/food-impact
  → SimulationService::simulateFoodImpact()
    → Gets your active Digital Twin
    → Calculates glucose curve using GlucoseCurveService
    → Applies your personal modifiers (stress, sleep, insulin resistance)
    → ✅ BEDROCK CALL: generateFoodAnalysis()
      System prompt: "You are a nutritional analyst..."
      User message: Food item, condition context, knowledge base excerpts
      Response: JSON with explanation + healthier alternatives array
```

**What you see**:
- **Glucose Response Curve** (Chart.js line chart): Predicted blood sugar over 3 hours
- **Peak glucose**: e.g., 186 mg/dL
- **Peak time**: e.g., 45 minutes
- **Your Modifiers**: "Sleep: -6h (reduced sensitivity)", "Stress: High (+glucose)"
- **AI Explanation**: "White rice has a high glycemic index (73). Given your current insulin resistance and high stress levels, your post-meal glucose is expected to spike significantly..."
- **Healthier Alternatives** (from Bedrock): "Brown rice (GI: 50)", "Quinoa (GI: 53)", "Cauliflower rice (GI: 15)"
- **Risk Alerts**: Generated warnings about the impact

#### Story E: Compare Two Foods (No Bedrock)
1. In the comparison section, select Food A: `White Rice` and Food B: `Brown Rice`
2. Click **Compare**

**What happens**: `POST /api/food-compare` → pure math glucose curve comparison (no Bedrock)

**What you see**:
- Two overlaid glucose curves on one chart
- Comparison table: spike difference, peak time, recovery time
- "Better choice" recommendation

---

### PHASE 8 — RAG Knowledge Query 🤖 **[BEDROCK REQUIRED]**

**Page**: `http://127.0.0.1:8000/knowledge`

#### Story F: Ask About Diabetes
1. Type question: `How does stress affect blood sugar levels in Type 2 Diabetes?`
2. Select disease context: `Diabetes`
3. Toggle **streaming** ON (for real-time response)
4. Click **Ask**

**What happens**:
```
POST /api/rag/query-stream (SSE)
  → RagController::stream()
    → RagSearchService::search() finds relevant knowledge base pages
    → ✅ BEDROCK CALL: BedrockService::stream()
      System prompt: "You are a medical knowledge synthesizer..."
      User message: RAG context excerpts + user's question
      Response: Streamed token-by-token via Server-Sent Events
```

**What you see**:
- **Streaming text** appearing word by word (like ChatGPT)
- **Confidence badge**: "High / Medium / Low" based on knowledge base match quality
- **Source Pages**: Citations from the RAG knowledge base
- **Reasoning Path**: Steps the RAG engine took to find the answer

#### Story G: Ask About PCOD
1. Type: `What foods should I avoid with PCOS to regulate my periods?`
2. Select context: `PCOD`
3. Click **Ask**

#### Story H: Ask a Lifestyle Question
1. Type: `What is the best time to eat dinner for managing blood sugar?`
2. Select context: `Diabetes`
3. Click **Ask**

---

### PHASE 9 — Dashboard & Alerts

**Page**: `http://127.0.0.1:8000/dashboard`

After running simulations, the dashboard shows:
- **Radar Chart**: 5-axis view (Metabolic, Insulin, Sleep, Stress, Diet)
- **Risk History Line Chart**: Trend of overall risk over time
- **Score Breakdown Cards**: Individual metric scores with progress bars

**Page**: `http://127.0.0.1:8000/alerts`

- Lists all AI-generated alerts from simulations
- Each alert has severity (info/warning/danger/critical)
- Alert messages are **AI-enhanced** (Bedrock adds actionable recommendations)
- Mark as read / mark all read

**Page**: `http://127.0.0.1:8000/history`

- Lists all past simulations
- Can **rerun** any simulation to see updated results with current twin data
- Can view detailed results of each past simulation

---

## User Story Matrix

| # | Story | Pages Visited | Bedrock Calls | Expected AI Output |
|---|-------|--------------|:--:|---|
| A | "What if I eat biryani at midnight?" | Simulations | 2 | Risk explanation + alerts |
| B | "What if I only sleep 4 hours?" | Simulations | 2 | Sleep impact on metabolism |
| C | "What if my stress increases?" | Simulations | 2 | Cortisol + insulin effect |
| D | "How does white rice affect my sugar?" | Food Impact | 1 | Glucose prediction + alternatives |
| E | "Rice vs. Quinoa — which is better?" | Food Impact | 0 | Pure math glucose comparison |
| F | "How does stress affect blood sugar?" | Knowledge | 1 | Streamed RAG answer |
| G | "Best foods for PCOS?" | Knowledge | 1 | Streamed RAG answer |
| H | "Best dinner time for blood sugar?" | Knowledge | 1 | Streamed RAG answer |

---

## Troubleshooting Bedrock Errors

| Error | Cause | Fix |
|-------|-------|-----|
| "AI service is currently unavailable" | Bedrock credentials not loaded | Go to Admin → AI Dashboard → re-save credentials, test connection |
| "Access denied" (403) | Invalid or expired bearer token | Generate a new ABSK token from AWS console |
| "Could not resolve host" | Wrong region or no internet | Verify region matches your Bedrock access |
| "AI service temporarily unavailable due to usage limits" | Cost limit exceeded | Admin → AI Dashboard → increase daily/monthly limits |
| Empty simulation explanation | Bedrock returned empty response | Check model alias config — ensure default model is set |
| "No active digital twin found" | Twin not generated yet | Go to Digital Twin page → click Generate |
| RAG query returns low confidence | Knowledge base doesn't have relevant data | Check if disease-specific RAG documents are seeded |

---

## Quick Test Script (5-Minute Verification)

Run these steps rapidly to verify the complete Bedrock integration:

1. **Register** → `http://127.0.0.1:8000/register` (new user)
2. **Health Profile** → fill with sample data above
3. **Disease Data** → `/disease/diabetes` — fill diabetes fields
4. **Generate Twin** → `/digital-twin` → click Generate → verify scores appear
5. **Simulate Meal** → `/simulations` → Meal + "Pizza" + "Late night" → Run → ✅ verify AI explanation appears
6. **Food Impact** → `/food-impact` → "White Rice" → Analyze → ✅ verify glucose curve + AI explanation
7. **RAG Query** → `/knowledge` → "How does sleep affect blood sugar?" → ✅ verify streamed answer
8. **Dashboard** → `/dashboard` → verify radar chart + risk history populated
9. **Alerts** → `/alerts` → verify alerts from simulation appear with AI-enhanced messages

If steps 5, 6, 7 succeed with AI-generated text → **Bedrock integration is fully working**.

---

## Data Flow Summary

```
User Input → Health Profile + Disease Data
                    ↓
            Digital Twin (Risk Engine — pure math)
                    ↓
         ┌──────────┼──────────────┐
         ↓          ↓              ↓
    Simulation   Food Impact    RAG Query
    (Bedrock)    (Bedrock)     (Bedrock)
         ↓          ↓              ↓
    Explanation  Glucose Curve   Streamed
    + Alerts     + Alternatives  Answer
         ↓          ↓              ↓
      Dashboard ← History ← Alerts (AI-enhanced)
```
