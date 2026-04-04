# HormoneLens — See Your Health Before You Live It!

**Team:** UBXTY — Unboxing Technology  
**Team Leader:** Ravdeep Singh

> India is the "Diabetes Capital of the World," and 1 in 5 Indian women suffer from PCOS. Current health apps are reactive — they track symptoms *after* they occur and use generic, linear rules that fail to account for the fact that metabolic health is non-linear. Stress and food affect every individual differently.

---

## What is HormoneLens?

HormoneLens is an **AI-powered Hormonal Digital Twin**. Instead of just logging past data, it creates a **virtual mirror of the user's metabolism**, allowing users to *counterfactualize* their health — simulating how a specific meal, missed sleep, or stress spike will impact their glucose and cortisol levels **before** they make the decision.

This shifts healthcare from **Reactive Tracking → Predictive Simulation**.

### What Can Users Simulate?

| Simulation Output | For PCOS Users | For Diabetic Users | For Thyroid Users |
|---|---|---|---|
| Insulin Sensitivity | Predict Androgen Imbalance | Predict Glucose Spike | Detect Metabolic Slowdown |
| Sleep Cycle Impact | Cycle Delay Risk Forecast | Fasting Sugar Variation | TSH Instability Risk |
| Meal Timing Effect | Ovulation Stability Risk | Post-meal Sugar Level | Weight Gain Propensity |
| Stress Level Simulation | Cortisol Imbalance Detection | Insulin Resistance Risk | Fatigue & Hormonal Imbalance Risk |
| Physical Activity Variation | Period Regularity Prediction | HbA1c Trend Forecast | Metabolic Rate Impact |
| Long-Term Outcome Prediction | PCOS Severity Progression | Diabetes Complication Risk | Hypo/Hyperthyroid Progression Risk |

---

## Solution Architecture

### How It Works

1. **User onboards** with health profile (PCOS / Diabetes / Thyroid conditions, lifestyle inputs).
2. **Digital Twin is created** — a personalized virtual replica of the user's metabolic state.
3. **What-If Simulations** — user tests lifestyle choices (meals, sleep, stress) and receives predictive outcomes.
4. **Risk Scoring & Alerts** — real-time metabolic risk scores with proactive alerts.
5. **Explainable Insights** — AI explains *why* a lifestyle choice may increase metabolic risk.

### AWS Services Powering HormoneLens

| AWS Service | Role |
|---|---|
| **Amazon Bedrock** | Core AI reasoning engine — powers the Digital Twin to simulate non-linear metabolic responses, predict hormonal imbalance, and generate glucose risk forecasts from lifestyle inputs. Uses **Claude 3.5 Sonnet** to translate complex biological risks into natural language "Preventive Insights." |
| **Amazon Bedrock Knowledge Bases (RAG)** | Retrieves personalized metabolic history using vector search to enable user-specific predictive simulations. |
| **Amazon Bedrock Guardrails** | Filters unsafe or misleading AI-generated health outputs. Ensures safe, non-diagnostic wellness guidance — eliminating medical hallucinations. |
| **Amazon EC2** | Hosts the Laravel backend and simulation engine. Handles AI request orchestration and API processing before calling Amazon Bedrock services. Provides scalable compute infrastructure for running Digital Twin simulations. |
| **Amazon API Gateway** | Securely handles user simulation requests and connects the frontend dashboard to the AI backend. |
| **Amazon RDS (PostgreSQL)** | Stores user profiles, health logs, and maintains historical simulation outcomes. |

### LLM Integration Details

| LLM Feature | AWS Service / Model | Role |
|---|---|---|
| Foundation Engine | Amazon Bedrock | Complex metabolic reasoning and "What-If" simulations |
| Retrieval (RAG) | Amazon Bedrock Knowledge Bases | Connects LLM to personalized historical context |
| Safety & Privacy | Amazon Bedrock Guardrails | Responsible, non-diagnostic wellness guidance |
| Response Synthesis | Claude 3.5 Sonnet | Natural language "Preventive Insights" generation |

---

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Flutter (REST API)
- **AI Engine:** Amazon Bedrock + Claude 3.5 Sonnet
- **Database:** Amazon RDS (PostgreSQL)
- **Hosting:** Amazon EC2
- **Authentication:** Laravel Sanctum
- **Architecture:** Service + Repository pattern
- **AI Package:** `ubxty/bedrock-ai`

---

## Key Features

- **Hormonal Digital Twin** — Personalized virtual metabolic replica
- **Predictive Simulation Engine** — "What-If" lifestyle simulations with glucose curve modeling
- **Metabolic Risk Scoring** — Real-time risk categories (low, moderate, high, critical)
- **Proactive Alert System** — Triggered on risk thresholds, high GI foods, low sleep, high stress
- **Lightweight RAG** — Keyword-based retrieval for Diabetes, PCOD, and Lifestyle/Nutrition data
- **Bedrock Guardrails** — Input sanitization & response validation for safe health guidance
- **Admin Dashboard** — User management, risk analytics, simulation data, and report generation

---

## Value the AI Layer Adds

- **Predictive Insights** — Simulate future hormonal and glucose responses before making lifestyle decisions
- **Hyper-Personalization** — Health predictions based on individual lifestyle patterns and metabolic history
- **Real-Time "What-If" Simulation** — Test lifestyle choices like meals or sleep changes instantly
- **Explainable Recommendations** — AI explains *why* a choice may increase metabolic risk
- **Behavioral Nudging** — Proactive alerts guiding healthier daily habits
- **Reduced Health Anxiety** — Replaces guesswork with data-backed simulation results

---

## Prototype Performance

| Metric | Value |
|---|---|
| Digital Twin Creation Time | 2–3 seconds |
| Simulation Execution Time | 2–4 seconds |
| Risk Score Generation Latency | < 1 second |
| Real-Time Alert Triggering | < 1 second |
| Food Impact Simulation Time | 2–3 seconds |
| Dashboard Update Delay | < 500 ms |
| Simulation History Retrieval | < 1 second |
| Concurrent User Support | 10–15 users (prototype) |

---

## Expected Impact

- **Reactive → Proactive** — Prevents spikes before they happen, potentially reducing HbA1c levels by 15–20% in early users
- **Democratizing Specialized Care** — Endocrinologist-level logic accessible to millions of Indian women with PCOS who cannot afford frequent doctor visits
- **Behavioral Nudging** — Simulation-driven self-correction of lifestyle choices in real-time
- **Safe & Responsible AI** — AWS Guardrails eliminate medical hallucinations, ensuring trust and safety
- **Eliminating Food Anxiety** — Replaces "Can I eat this?" fear with clear simulation-backed certainty

---

## Future Scope

- Integration with wearable health devices
- Real-time glucose monitoring support
- Mobile application development
- Clinical dataset integration
- AI-based doctor recommendation system
- Expansion to additional metabolic disorders

---

## Getting Started

The Laravel application lives in the `backend/` directory. You can run commands from the repository root:

```sh
# proxy script shipped at repository root
php artisan serve
# or equivalent
./artisan serve

# explicitly specify path
php backend/artisan serve

# or change into the backend directory
cd backend && php artisan serve
```

Make sure the script is executable (`chmod +x artisan`).

---

## Links

- **Website:** [https://hormonelens.com](https://hormonelens.com)
- **GitHub:** [https://github.com/ubxty/HormoneLens](https://github.com/ubxty/HormoneLens)
- **Demo Video:** [https://youtu.be/zSXGp9NnPgM](https://youtu.be/zSXGp9NnPgM?si=7w-X_jXGfOwSwvOI)

---

## Credits & Acknowledgements

- **[Amazon Web Services (AWS)](https://aws.amazon.com/)** — Amazon Bedrock, EC2 and Bedrock Guardrails power the entire AI and infrastructure layer
- **[CloudPanzer](https://cloudpanzer.com/app)** — Used to deploy and manage the solution on cloud infrastructure
- **[Laravel](https://laravel.com/)** — Backend framework
 
