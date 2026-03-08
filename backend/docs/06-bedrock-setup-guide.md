# Amazon Bedrock Setup Guide — HormoneLens

Complete step-by-step instructions to set up AWS Bedrock AI, configure it in the admin panel, and verify it works end-to-end.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [AWS Account Setup](#2-aws-account-setup)
3. [Enable Bedrock Model Access](#3-enable-bedrock-model-access)
4. [Create an IAM User for Bedrock](#4-create-an-iam-user-for-bedrock)
5. [Configure .env (Optional — Server-Level)](#5-configure-env-optional--server-level)
6. [Admin Panel — Login & Navigate to AI Dashboard](#6-admin-panel--login--navigate-to-ai-dashboard)
7. [Admin Panel — Enter AWS Credentials](#7-admin-panel--enter-aws-credentials)
8. [Admin Panel — Test Connection](#8-admin-panel--test-connection)
9. [Admin Panel — Configure AI Settings](#9-admin-panel--configure-ai-settings)
10. [Test Bedrock End-to-End](#10-test-bedrock-end-to-end)
11. [User Stories & Acceptance Criteria](#11-user-stories--acceptance-criteria)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. Prerequisites

Before starting, ensure you have:

- [ ] **An AWS Account** — [Create one here](https://aws.amazon.com/) if you don't have one
- [ ] **HormoneLens backend running** — `php artisan serve` (default: `http://localhost:8000`)
- [ ] **Database migrated & seeded** — `php artisan migrate --seed`
- [ ] **Admin user exists** — Created by `AdminUserSeeder` (email: `admin@hormonelens.com`, password: `admin123`)

### Supported AWS Regions for Bedrock

Bedrock is available in select regions. Recommended regions:

| Region | Code | Notes |
|--------|------|-------|
| US East (N. Virginia) | `us-east-1` | Most models available, **recommended** |
| US West (Oregon) | `us-west-2` | Good availability |
| Asia Pacific (Mumbai) | `ap-south-1` | Closest for India-based users |
| Europe (Frankfurt) | `eu-central-1` | EU data residency |

---

## 2. AWS Account Setup

If you already have an AWS account, skip to Step 3.

1. Go to [https://aws.amazon.com/](https://aws.amazon.com/)
2. Click **Create an AWS Account**
3. Enter your email, set a password, and choose an account name
4. Provide payment information (required even for free tier)
5. Complete identity verification
6. Select the **Basic Support** plan (free)
7. Sign in to the **AWS Management Console**

---

## 3. Enable Bedrock Model Access

By default, Bedrock models are **not enabled**. You must request access for each model you want to use.

### Step-by-step:

1. Sign in to the [AWS Console](https://console.aws.amazon.com/)
2. In the top navigation bar, select your region (e.g., **US East (N. Virginia)**)
3. Search for **"Amazon Bedrock"** in the service search bar and open it
4. In the left sidebar, click **Model access**
5. Click **Manage model access** (orange button, top-right)
6. **While here, also copy your Bearer Token** — look for an option like **"API keys"** or the shell export snippet:
   ```bash
   export AWS_BEARER_TOKEN_BEDROCK=ABSKYmV...
   ```
   Copy everything after the `=` — this is what you'll paste into the admin panel.
7. Enable the following models:

   | Model | Model ID | Purpose in HormoneLens |
   |-------|----------|----------------------|
   | **Claude 3.5 Sonnet v2** | `anthropic.claude-3-5-sonnet-20241022-v2:0` | Default reasoning model (simulation explanations, RAG synthesis) |
   | **Claude 3 Haiku** | `anthropic.claude-3-haiku-20240307-v1:0` | Fast model (quick tasks, fallback on rate limits) |

7. Click **Request model access**
8. Wait for status to change to **Access granted** (usually instant for Anthropic models, may take a few minutes)

> **Important:** If either model shows "Requires approval", it means your account needs manual review. This typically takes 24-48 hours.

---

## 4. Get Credentials — Bearer Token (Recommended) or IAM Keys

HormoneLens supports **two auth methods**. Use whichever your AWS account provides:

### Option A — Bearer Token (Recommended, simpler)

The AWS Bedrock console provides a pre-generated bearer token that works directly with the Bedrock API — no IAM user or secret key needed.

1. In the Bedrock Console, look for **API keys**, **Temporary credentials**, or a shell snippet like:
   ```bash
   export AWS_BEARER_TOKEN_BEDROCK=ABSKYmVkc...
   ```
2. Copy the entire token value (starts with `ABSK`)
3. You will paste this into the admin panel in Step 7

### Option B — IAM Access Keys (Traditional)

Use this if you don't have a bearer token or prefer long-lived credentials.

1. Go to the [IAM Console](https://console.aws.amazon.com/iam)
2. Click **Users** → **Create user** → Name: `hormonelens-bedrock`
3. Attach policy: `AmazonBedrockFullAccess`
4. After creation → **Security credentials** tab → **Create access key** → **Application running outside AWS**
5. **Save both values:**

   | Field | Example |
   |-------|--------|
   | **Access Key ID** | `AKIA5XYZ1234ABCD5678` |
   | **Secret Access Key** | `wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY` |

   > **Warning:** The secret key is shown only once. Save it immediately.

---

## 5. Configure .env (Optional — Server-Level)

You can configure credentials either via `.env` file (server-level) **or** via the Admin Panel (database-level). The Admin Panel method is recommended as it doesn't require restarting the app.

To use `.env`, add these to your `.env` file:

```env
# ── Amazon Bedrock ────
AWS_ACCESS_KEY_ID=AKIA5XYZ1234ABCD5678
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
AWS_DEFAULT_REGION=us-east-1

BEDROCK_AWS_KEY="${AWS_ACCESS_KEY_ID}"
BEDROCK_AWS_SECRET="${AWS_SECRET_ACCESS_KEY}"
BEDROCK_REGION=us-east-1
BEDROCK_DAILY_LIMIT=10.00
BEDROCK_MONTHLY_LIMIT=100.00
BEDROCK_LOGGING_ENABLED=true
```

Then clear the config cache:

```bash
php artisan config:clear
```

> **Recommended:** Use the Admin Panel method (Step 7) instead. It stores credentials in the database and overrides the `.env` values at runtime, so you don't need to restart the app or clear caches.

---

## 6. Admin Panel — Login & Navigate to AI Dashboard

### Login to Admin Panel

1. Open your browser and go to:

   ```
   http://localhost:8000/admin/login
   ```

2. Enter the default admin credentials:

   | Field | Value |
   |-------|-------|
   | **Email** | `admin@hormonelens.com` |
   | **Password** | `admin123` |

3. Click **Login**

### Navigate to AI Dashboard

4. In the admin sidebar, click **🤖 AI / Bedrock** (or navigate directly to):

   ```
   http://localhost:8000/admin/bedrock
   ```

5. You should see the **AI Dashboard** with:
   - **Bedrock Status** card — showing "Disconnected" (red dot) if no credentials are set
   - **AWS Credentials** card — showing "Not configured"
   - **Settings** grid — showing AI feature toggles

---

## 7. Admin Panel — Enter Credentials

1. On the AI Dashboard (`/admin/bedrock`), find the **AWS Credentials** card
2. Click **Configure** (or **Update** if already set)
3. Select your **Auth Method** using the toggle:

### Bearer Token mode (Recommended)

| Field | What to enter |
|-------|--------------|
| **Bearer Token** | Paste the `ABSK...` token from the Bedrock console |
| **AWS Region** | Select the region where your Bedrock models are enabled |

### Access Keys mode

| Field | What to enter |
|-------|--------------|
| **AWS Access Key ID** | Your IAM user's Access Key (starts with `AKIA...`) |
| **AWS Secret Access Key** | Your IAM user's Secret Key |
| **AWS Region** | Select the region where your Bedrock models are enabled |

4. Click **Save Credentials**
5. You should see a success toast notification
6. The card now shows the masked token/key and the Auth Mode badge

### What happens behind the scenes:

- Credentials are stored in the `ai_settings` database table (`bedrock_aws_key`, `bedrock_aws_secret`, `bedrock_region`)
- The `ubxty/bedrock-ai` package auto-detects bearer mode: if `aws_key` starts with `ABSK`, it sends `Authorization: Bearer <token>` directly to the Bedrock REST API — no AWS SDK signing required
- Runtime config is updated immediately — no app restart needed

---

## 8. Admin Panel — Test Connection

1. On the AI Dashboard, find the **Bedrock Status** card
2. Click the **Test Connection** button
3. Wait 3-5 seconds for the response

### Expected Results

**If successful:**
- Status dot turns **green** ✅
- Status text shows **"Connected"**
- Test result shows response details (model used, latency, token count)

**If failed:**
- Status dot stays **red** ❌
- Error message appears explaining the issue (see [Troubleshooting](#12-troubleshooting))

---

## 9. Admin Panel — Configure AI Settings

The Settings grid on the AI Dashboard controls which AI features are active:

### Feature Toggles

| Setting | Default | Description |
|---------|---------|-------------|
| **AI Enabled** | ✅ On | Master switch — disabling this turns off ALL AI features |
| **RAG AI Synthesis** | ✅ On | When ON, RAG queries get AI-synthesized answers from Bedrock. When OFF, returns raw KB text. |
| **Simulation AI Explanation** | ✅ On | When ON, food impact and simulation results include AI-generated explanations. When OFF, returns only numerical results. |
| **Alert AI Enhancement** | ✅ On | When ON, alert messages are enhanced with AI context. When OFF, uses template messages. |

### Model Settings

| Setting | Default | Description |
|---------|---------|-------------|
| **Default Model** | `default` | Alias for the primary model (`claude-3.5-sonnet`). Used for most AI tasks. |
| **Fast Model** | `fast` | Alias for the fast model (`claude-3-haiku`). Used for quick tasks and rate-limit fallback. |

### Cost Limits

| Setting | Default | Description |
|---------|---------|-------------|
| **Max Tokens** | `1024` | Maximum tokens per AI request |
| **Daily Cost Limit** | `$10` | Maximum daily spend. Requests are rejected when exceeded. |
| **Monthly Cost Limit** | `$100` | Maximum monthly spend. |

### How to change a setting:

- **Boolean toggles:** Click the toggle switch to flip ON/OFF
- **Other values:** Currently display-only in the dashboard. To change, use the API:

  ```bash
  curl -X PUT http://localhost:8000/api/admin/bedrock/settings \
    -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"settings": [{"key": "max_tokens", "value": "2048"}]}'
  ```

---

## 10. Test Bedrock End-to-End

After configuring credentials and verifying the connection, test the actual AI features:

### Test 1: Food Impact with AI Explanation

1. Log in as a regular user (register at `/register` or use test credentials)
2. Complete the **Health Profile** at `/health-profile`
3. Fill in a **Disease Profile** (Diabetes or PCOS)
4. **Generate a Digital Twin** at `/digital-twin` → click "Generate"
5. Navigate to **Food Impact** at `/food-impact`
6. Enter a food item (e.g., `White Rice`) and select a meal time
7. Click **Analyze**

**Expected:** The result includes:
- Glucose response curve chart
- AI-generated explanation (starts with a personalized insight about YOUR health data)
- Cross-factor modifiers (how sleep, stress, activity affect your response)
- Healthier alternatives

### Test 2: Food Comparison

1. On the Food Impact page, scroll to the **Compare Foods** section
2. Enter two foods (e.g., `White Rice` and `Brown Rice`)
3. Select a meal time
4. Click **Compare**

**Expected:** Side-by-side glucose curves with a "better choice" indicator.

### Test 3: RAG Query with AI Synthesis

1. Navigate to **Knowledge Base** at `/rag-query`
2. Enter a query like: `How does sleep affect insulin resistance?`
3. Click **Search**

**Expected:**
- AI-synthesized answer that references your health profile
- Source citations from the knowledge base
- Streaming response (text appears word-by-word)

### Test 4: View Models (Admin)

1. In the Admin Panel, go to the AI Dashboard
2. Click **🧠 View Models**

**Expected:** A list of all Bedrock models available in your region, including the Claude models you enabled in Step 3.

### Test 5: Usage & Costs (Admin)

1. In the Admin Panel AI Dashboard, click **💰 Usage & Costs**

**Expected:** Current usage statistics including:
- Number of invocations
- Token counts (input/output)
- Estimated cost

---

## 11. User Stories & Acceptance Criteria

### US-1: Admin configures AWS Bedrock credentials

**As an** admin  
**I want to** enter my AWS credentials in the admin panel  
**So that** the Bedrock AI features are activated without modifying server files

**Acceptance Criteria:**
- [ ] Admin can navigate to `/admin/bedrock`
- [ ] Credentials form accepts AWS Access Key ID, Secret, and Region
- [ ] Validation rejects keys shorter than 16 characters
- [ ] Saved credentials are immediately active (no restart needed)
- [ ] Credentials are stored securely in the database
- [ ] Keys are masked in the UI (show first 4 and last 4 characters only)
- [ ] Previously saved credentials can be updated

---

### US-2: Admin tests Bedrock connection

**As an** admin  
**I want to** test the Bedrock connection from the dashboard  
**So that** I can confirm the credentials are valid and the service is reachable

**Acceptance Criteria:**
- [ ] "Test Connection" button sends a test prompt to Bedrock
- [ ] Success shows green status + response latency
- [ ] Failure shows red status + specific error message
- [ ] Test works immediately after saving credentials (same page, no reload)

---

### US-3: Admin toggles AI features on/off

**As an** admin  
**I want to** enable or disable individual AI features  
**So that** I can control which parts of the app use Bedrock (and manage costs)

**Acceptance Criteria:**
- [ ] Master "AI Enabled" toggle disables all AI globally
- [ ] Individual toggles control: RAG synthesis, simulation explanations, alert enhancement
- [ ] When a feature is disabled, its endpoint falls back to non-AI behavior
- [ ] Changes take effect immediately

---

### US-4: User receives AI-powered food impact analysis

**As a** user with a health profile and digital twin  
**I want to** see an AI-generated explanation when I analyze a food item  
**So that** I understand WHY a food affects my health (not just the numbers)

**Acceptance Criteria:**
- [ ] Food impact response includes `rag_explanation` with a personalized AI insight
- [ ] Explanation references user's actual health data (sleep hours, stress level, blood sugar)
- [ ] Glucose curve chart shows predicted spike visualization
- [ ] Cross-factor modifiers (sleep, stress, activity, meal time) are displayed
- [ ] Works for 79+ Indian foods from the glycemic database
- [ ] Falls back to numeric-only results when AI is disabled

---

### US-5: User queries the AI-enhanced knowledge base

**As a** user  
**I want to** ask health questions and get AI-synthesized answers  
**So that** I receive personalized, contextual health information

**Acceptance Criteria:**
- [ ] RAG query page accepts natural language questions
- [ ] AI synthesizes answers from KB content + user's health profile
- [ ] Response streams in real-time (SSE) for better UX
- [ ] Source citations reference the KB document and page
- [ ] Medical disclaimer is included in every AI response
- [ ] When AI is disabled, returns raw KB search results without synthesis

---

### US-6: Admin monitors AI usage and costs

**As an** admin  
**I want to** view Bedrock usage statistics and cost estimates  
**So that** I can monitor spending and stay within budget

**Acceptance Criteria:**
- [ ] Usage page shows invocation count, token usage, estimated cost
- [ ] Daily and monthly cost limits are configurable
- [ ] Requests are rejected when cost limits are exceeded
- [ ] Admin receives a clear error when limits are hit

---

## 12. Troubleshooting

### "Disconnected" after saving credentials

| Possible Cause | Solution |
|----------------|----------|
| Wrong credentials | Double-check the Access Key ID and Secret. Re-copy from IAM console. |
| Wrong region | The region must match where you enabled Bedrock model access (Step 3). |
| Model not enabled | Go to Bedrock Console → Model access → Ensure Claude models are "Access granted". |
| IAM permissions | Ensure the IAM user has `AmazonBedrockFullAccess` policy attached. |

### "Rate limit exceeded" errors

- The `fast` model (Claude 3 Haiku) is used as automatic fallback on rate limits
- If both models are rate-limited, wait 30 seconds and try again
- Consider increasing your AWS Service Quotas for Bedrock

### "Cost limit exceeded" error

- Check the daily/monthly limits in AI Settings
- Increase limits via the settings API or wait for the next day/month reset
- Current defaults: $10/day, $100/month

### "Model not found" error

| Cause | Solution |
|-------|----------|
| Model not enabled in your region | Go to Bedrock Console → Model access → Enable Claude 3.5 Sonnet and Claude 3 Haiku |
| Using wrong region | Ensure your admin credentials region matches the Bedrock Console region |
| Model ID changed | Check `config/bedrock.php` model aliases match current AWS model IDs |

### AI features not generating explanations

1. Check the AI Dashboard — is **AI Enabled** toggled ON?
2. Check the specific feature toggle (RAG AI Synthesis, Simulation AI Explanation)
3. Verify the connection by clicking **Test Connection**
4. Check logs: `storage/logs/laravel.log` for Bedrock-related errors

### Credentials not persisting after restart

Credentials entered via the Admin Panel are stored in the `ai_settings` database table and loaded at boot time by `RagServiceProvider`. They survive restarts automatically. If they're missing:

1. Check the database: `SELECT * FROM ai_settings WHERE key LIKE 'bedrock%';`
2. Verify `RagServiceProvider` is registered in `config/app.php` or `bootstrap/providers.php`

---

## Quick Reference — Admin API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/admin/bedrock/status` | Connection status + settings |
| `POST` | `/api/admin/bedrock/test` | Test Bedrock connection |
| `GET` | `/api/admin/bedrock/credentials` | View masked credentials |
| `PUT` | `/api/admin/bedrock/credentials` | Save AWS credentials |
| `GET` | `/api/admin/bedrock/settings` | Get all AI settings |
| `PUT` | `/api/admin/bedrock/settings` | Update AI settings |
| `GET` | `/api/admin/bedrock/models` | List available Bedrock models |
| `GET` | `/api/admin/bedrock/usage` | Usage & cost statistics |
| `GET` | `/api/admin/bedrock/pricing` | Model pricing info |

All admin API endpoints require authentication: `Authorization: Bearer <admin_sanctum_token>` and the `superadmin` middleware.

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────┐
│                   Admin Panel                        │
│           /admin/bedrock (Web UI)                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────────────────┐ │
│  │  Status   │ │  Creds   │ │   Settings Toggles   │ │
│  │  Card     │ │  Form    │ │  AI On/Off per feat. │ │
│  └────┬─────┘ └────┬─────┘ └──────────┬───────────┘ │
└───────┼────────────┼──────────────────┼─────────────┘
        │            │                  │
        ▼            ▼                  ▼
┌──── Admin API (routes/adminapi.php) ─────────────────┐
│  GET /status  │  PUT /creds  │  PUT /settings        │
│  POST /test   │  GET /creds  │  GET /settings        │
└───────┬───────┴──────┬───────┴──────┬────────────────┘
        │              │              │
        ▼              ▼              ▼
┌─── BedrockManagement ──┐   ┌── AiSetting Model ──┐
│   Controller            │   │  getValue/setValue   │
│   testConnection()      │   │  DB: ai_settings     │
│   credentials()         │   │  Grouped by type     │
└──────────┬──────────────┘   └──────────┬──────────┘
           │                             │
           ▼                             ▼
┌─── BedrockService ──────────────────────────────────┐
│  ask(systemPrompt, userMessage, options)             │
│  stream(systemPrompt, userMessage, onChunk, options) │
│  testConnection() / isAvailable() / listModels()     │
│                                                      │
│  ┌─ GuardrailService ─┐  ┌─ ubxty/bedrock-ai ────┐ │
│  │  sanitizeInput()    │  │  Bedrock::invoke()     │ │
│  │  validateResponse() │  │  Bedrock::stream()     │ │
│  └────────────────────┘  └────────────────────────┘ │
└──────────────────────────┬───────────────────────────┘
                           │
                           ▼
             ┌── AWS Bedrock API ──┐
             │  Claude 3.5 Sonnet  │
             │  Claude 3 Haiku     │
             └─────────────────────┘
```

### Data Flow: Credential Lifecycle

```
1. Admin enters creds in UI
2. PUT /api/admin/bedrock/credentials
3. BedrockManagementController validates & stores in ai_settings table
4. Runtime config updated immediately: config(['bedrock.connections.default.keys' => ...])
5. On next app boot: RagServiceProvider reads ai_settings → overrides config
6. BedrockService reads config('bedrock.connections.default.keys') for every API call
```
