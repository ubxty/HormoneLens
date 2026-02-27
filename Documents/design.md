# HormoneLens - Technical Design Document

## 1. High-Level Architecture

### 1.1 System Overview

HormoneLens follows a three-tier architecture with AI-powered prediction capabilities:

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER LAYER                               │
│  (Web Browser - Blade Templates + JavaScript + WebSocket)       │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTPS/WSS
┌────────────────────────────▼────────────────────────────────────┐
│                    APPLICATION LAYER                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │   Laravel Backend (AWS EC2)                               │  │
│  │   - API Controllers                                       │  │
│  │   - Business Logic Services                              │  │
│  │   - Laravel Reverb (WebSocket Server)                    │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────┬──────────────────────┬──────────────────┬──────────────┘
        │                      │                  │
        │ SQL                  │ HTTP API         │ Vector Query
        ▼                      ▼                  ▼
┌──────────────────┐  ┌─────────────────────┐  ┌──────────────────┐
│  Amazon RDS      │  │  Amazon Bedrock     │  │  ChromaDB        │
│  (PostgreSQL)    │  │  - Foundation       │  │  (Vector Store)  │
│  - User Data     │  │    Models (Auto)    │  │  - Embeddings    │
│  - Health Logs   │  │  - Guardrails       │  │  - RAG Context   │
└──────────────────┘  └─────────────────────┘  └──────────────────┘
```

### 1.2 Architecture Flow Description

1. **User Interface**: Blade templates render the dashboard with real-time updates via Laravel Reverb WebSocket
2. **Application Layer**: Laravel backend orchestrates all business logic, API routing, and service coordination
3. **Data Layer**: PostgreSQL stores structured data; ChromaDB stores vector embeddings for RAG
4. **AI Layer**: Amazon Bedrock processes predictions with safety guardrails
5. **Real-Time Layer**: Laravel Reverb pushes live updates to connected clients

---

## 2. Component Details

### 2.1 Backend (Laravel on AWS EC2)

#### 2.1.1 Technology Stack
- **Framework**: Laravel 10.x (PHP 8.2+)
- **Hosting**: AWS EC2 (t3.medium or t3.large instance)
- **Web Server**: Nginx with PHP-FPM
- **Process Manager**: Supervisor (for queue workers and Reverb)

#### 2.1.2 Core Services

**SimulationService**
- Orchestrates the entire prediction pipeline
- Coordinates retrieval, prompt construction, and AI inference
- Returns structured prediction results

**RAGService**
- Queries ChromaDB for relevant historical context
- Generates embeddings for new health logs
- Manages vector similarity search

**BedrockService**
- Handles AWS Bedrock API communication
- Implements dynamic model selection based on availability
- Manages multi-model validation for high-risk scenarios
- Implements retry logic with exponential backoff
- Manages guardrail validation
- Parses AI responses into structured format
- Logs model performance metrics

**MetabolicScoreService**
- Calculates daily metabolic score (0-100)
- Weighs sleep (30%), stress (30%), diet (40%)
- Updates score in real-time as logs are added

**HealthLogService**
- CRUD operations for health logs
- Validates input data
- Triggers embedding generation for RAG

#### 2.1.3 Laravel Reverb (WebSocket)
- **Purpose**: Real-time updates for simulation results and score changes
- **Events**:
  - `SimulationCompleted`: Broadcasts prediction results
  - `MetabolicScoreUpdated`: Broadcasts score changes
- **Channels**: Private channels per user (`user.{userId}`)

### 2.2 AI Engine (Amazon Bedrock)

#### 2.2.1 Model Configuration
- **Primary Model**: Auto-selected based on latest available intelligent model in Bedrock
- **Model Selection Strategy**: Dynamic selection from available foundation models
- **Region**: `us-east-1` (or closest to deployment)
- **Max Tokens**: 1024
- **Temperature**: 0.3 (for consistent, factual predictions)
- **Model Candidates**: Anthropic Claude, Amazon Titan, Meta Llama, Cohere Command (evaluated at runtime)

#### 2.2.2 Bedrock Guardrails
- **Guardrail ID**: Created via AWS Console
- **Filters**:
  - **Content Filters**: Block medical misinformation, dangerous advice
  - **Denied Topics**: Medication dosage changes, diagnosis claims
  - **Word Filters**: Block terms like "cure", "guaranteed", "stop medication"
- **Action on Block**: Return safe fallback message

#### 2.2.3 Multi-Model Validation Strategy

**Validation Approach**:
- For critical predictions (High risk scenarios), query multiple models in parallel
- Compare responses for consistency and confidence
- Use consensus-based decision making when models disagree
- Log model performance metrics for continuous improvement

**Model Selection Logic**:
```
1. Primary Model: Latest high-intelligence model (auto-detected)
2. Validation Model: Secondary model for cross-validation (optional, for High risk)
3. Fallback Model: Backup if primary fails
```

**Comparison Criteria**:
- Risk level agreement (glucose/cortisol)
- Explanation coherence and medical accuracy
- Response time and cost efficiency
- Guardrail pass rate

#### 2.2.4 Prompt Engineering Strategy

**System Prompt Template**:
```
You are a metabolic health assistant for Indian users with PCOS/Diabetes.
Analyze the following scenario and predict glucose and cortisol impact.

User Profile: {age}, {condition}, {dietary_preferences}
Historical Context: {rag_retrieved_logs}
Current Scenario: {user_input}

Respond in JSON format:
{
  "glucose_risk": "High|Medium|Low",
  "glucose_range": "estimated mg/dL spike",
  "cortisol_risk": "High|Medium|Low",
  "explanation": "2-3 sentence reasoning",
  "alternatives": ["option1", "option2"]
}
```

### 2.3 Database (Amazon RDS - PostgreSQL)

#### 2.3.1 Configuration
- **Instance Type**: db.t3.micro (free tier) or db.t3.small
- **Storage**: 20GB SSD with auto-scaling enabled
- **Encryption**: AES-256 encryption at rest enabled
- **Backups**: Automated daily backups with 7-day retention
- **Multi-AZ**: Disabled for hackathon (enable for production)

#### 2.3.2 Connection Pooling
- Laravel uses `pgsql` driver with connection pooling
- Max connections: 100 (adjust based on EC2 instance size)

### 2.4 Vector Storage (ChromaDB)

#### 2.4.1 Deployment
- **Hosting**: Same EC2 instance as Laravel (separate Docker container)
- **Port**: 8000 (internal, not exposed publicly)
- **Persistence**: Volume mounted to `/chroma/data`

#### 2.4.2 Collections
- **Collection Name**: `health_logs_embeddings`
- **Embedding Model**: `all-MiniLM-L6-v2` (via sentence-transformers)
- **Metadata Stored**: `user_id`, `log_id`, `timestamp`, `log_type`

#### 2.4.3 Embedding Strategy
- Health logs are converted to text: `"Meal: {meal}, Stress: {stress}, Glucose: {glucose}, Symptoms: {symptoms}"`
- Embeddings generated using Python microservice (Flask API on EC2)
- Top 5 similar logs retrieved for RAG context

### 2.5 Frontend (Blade Templates + JavaScript)

#### 2.5.1 Technology
- **Templating**: Laravel Blade
- **Styling**: Tailwind CSS
- **Charts**: Chart.js for metabolic score trends
- **WebSocket Client**: Laravel Echo (JavaScript library)

#### 2.5.2 Key Views
- `dashboard.blade.php`: Main dashboard with score and simulation form
- `history.blade.php`: Historical logs table with filters
- `profile.blade.php`: User profile and health settings

---

## 3. Data Flow Architecture

### 3.1 The Simulation Pipeline (Step-by-Step)

#### Step 1: User Input
- **Trigger**: User submits form on dashboard
- **Input**: `{ "scenario": "2 slices of pizza + high work stress", "meal_type": "lunch" }`
- **Action**: Frontend sends POST request to `/api/simulate`

#### Step 2: Request Validation
- **Component**: `SimulationController@simulate`
- **Validation**:
  - `scenario` is required, max 500 characters
  - `meal_type` is optional, enum: breakfast|lunch|dinner|snack
- **Action**: If valid, pass to `SimulationService`

#### Step 3: RAG Retrieval
- **Component**: `RAGService@retrieveContext`
- **Process**:
  1. Generate embedding for user input scenario
  2. Query ChromaDB: `collection.query(query_embeddings=[input_embedding], n_results=5)`
  3. Retrieve top 5 similar past health logs
- **Output**: Array of historical logs with metadata

#### Step 4: Prompt Construction
- **Component**: `SimulationService@buildPrompt`
- **Process**:
  1. Fetch user profile (age, condition, dietary preferences)
  2. Format retrieved logs into readable context
  3. Combine system prompt + user profile + RAG context + current scenario
- **Output**: Complete prompt string for Bedrock

#### Step 5: Bedrock Inference
- **Component**: `BedrockService@predict`
- **Process**:
  1. Select optimal model from available Bedrock models
  2. Send prompt to Bedrock API with guardrail ID
  3. For High risk scenarios: Query secondary model for validation
  4. Compare results if multi-model validation is enabled
  5. Wait for response (timeout: 5 seconds)
  6. Parse JSON response from AI model
- **Output**: Structured prediction object (with validation metadata if applicable)

#### Step 6: Guardrail Validation
- **Component**: Bedrock Guardrails (automatic)
- **Process**:
  - If content violates guardrail: Return `{ "blocked": true, "reason": "unsafe_content" }`
  - If safe: Return AI prediction
- **Fallback**: If blocked, return safe message: "Please consult your healthcare provider"

#### Step 7: Response Storage
- **Component**: `SimulationService@storeResult`
- **Process**:
  1. Save prediction to `simulation_history` table
  2. Update user's daily metabolic score if meal is logged
- **Output**: Database record created

#### Step 8: Real-Time Broadcast
- **Component**: Laravel Reverb
- **Process**:
  1. Dispatch `SimulationCompleted` event
  2. Broadcast to user's private channel: `user.{userId}`
  3. Frontend receives event via Laravel Echo
- **Output**: Dashboard updates in real-time without page refresh

#### Step 9: Frontend Rendering
- **Component**: JavaScript event listener
- **Process**:
  1. Receive WebSocket event
  2. Update DOM with prediction results
  3. Render risk badges (High=Red, Medium=Yellow, Low=Green)
  4. Display alternatives if risk is High
- **Output**: User sees prediction within 3 seconds

### 3.2 Data Flow Diagram

```
User Input (Dashboard)
    │
    ▼
POST /api/simulate
    │
    ▼
SimulationController
    │
    ├──▶ RAGService ──▶ ChromaDB (Vector Search)
    │                      │
    │                      ▼
    │                  Top 5 Similar Logs
    │                      │
    ▼                      ▼
SimulationService ◀────────┘
    │
    ├──▶ Build Prompt (User Profile + RAG Context + Input)
    │
    ▼
BedrockService
    │
    ▼
Amazon Bedrock API
    │
    ├──▶ Model Selection (Auto-detect best available)
    │
    ├──▶ Guardrails Check
    │       │
    │       ├──▶ [BLOCKED] ──▶ Return Safe Message
    │       │
    │       └──▶ [SAFE] ──▶ Primary Model Inference
    │                           │
    │                           ├──▶ [High Risk] ──▶ Validation Model (Optional)
    │                           │                         │
    │                           │                         ▼
    │                           │                    Compare Results
    │                           │                         │
    │                           ▼◀────────────────────────┘
    │                      JSON Prediction
    │                           │
    ▼◀──────────────────────────┘
SimulationService
    │
    ├──▶ Save to simulation_history (PostgreSQL)
    │
    ├──▶ Update Metabolic Score
    │
    ▼
Laravel Reverb (Broadcast)
    │
    ▼
Frontend (WebSocket Update)
    │
    ▼
User Sees Result
```

---

## 4. Database Schema

### 4.1 Users Table

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    age INTEGER,
    gender VARCHAR(50),
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    health_condition VARCHAR(100), -- 'PCOS', 'Diabetes', 'Both'
    dietary_preference VARCHAR(100), -- 'Vegetarian', 'Non-Vegetarian', 'Vegan'
    medications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
```

### 4.2 Health Logs Table

```sql
CREATE TABLE health_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_type VARCHAR(50) NOT NULL, -- 'meal', 'sleep', 'stress', 'activity', 'symptom'
    meal_description TEXT,
    meal_type VARCHAR(50), -- 'breakfast', 'lunch', 'dinner', 'snack'
    stress_level INTEGER CHECK (stress_level BETWEEN 1 AND 10),
    sleep_hours DECIMAL(3,1) CHECK (sleep_hours BETWEEN 0 AND 24),
    activity_type VARCHAR(100),
    activity_duration INTEGER, -- minutes
    symptom_description TEXT,
    glucose_response INTEGER, -- mg/dL (if measured)
    notes TEXT,
    logged_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_health_logs_user_id ON health_logs(user_id);
CREATE INDEX idx_health_logs_logged_at ON health_logs(logged_at);
CREATE INDEX idx_health_logs_log_type ON health_logs(log_type);
```

### 4.3 Simulation History Table

```sql
CREATE TABLE simulation_history (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    input_scenario TEXT NOT NULL,
    meal_type VARCHAR(50),
    glucose_risk VARCHAR(20), -- 'High', 'Medium', 'Low'
    glucose_range VARCHAR(50), -- e.g., '140-180 mg/dL'
    cortisol_risk VARCHAR(20), -- 'High', 'Medium', 'Low'
    explanation TEXT,
    alternatives JSONB, -- Array of alternative suggestions
    rag_context JSONB, -- Retrieved logs used for context
    model_used VARCHAR(100), -- Bedrock model ID used for prediction
    validation_model VARCHAR(100), -- Secondary model used (if applicable)
    models_agreed BOOLEAN, -- Whether multiple models agreed (if validated)
    was_blocked BOOLEAN DEFAULT FALSE,
    block_reason TEXT,
    response_time_ms INTEGER, -- Track API performance
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_simulation_history_user_id ON simulation_history(user_id);
CREATE INDEX idx_simulation_history_created_at ON simulation_history(created_at);
CREATE INDEX idx_simulation_history_model_used ON simulation_history(model_used);
```

### 4.4 Model Performance Table

```sql
CREATE TABLE model_performance (
    id BIGSERIAL PRIMARY KEY,
    model_id VARCHAR(100) NOT NULL,
    total_requests INTEGER DEFAULT 0,
    successful_requests INTEGER DEFAULT 0,
    failed_requests INTEGER DEFAULT 0,
    avg_response_time_ms INTEGER,
    guardrail_blocks INTEGER DEFAULT 0,
    high_risk_predictions INTEGER DEFAULT 0,
    medium_risk_predictions INTEGER DEFAULT 0,
    low_risk_predictions INTEGER DEFAULT 0,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(model_id)
);

CREATE INDEX idx_model_performance_model_id ON model_performance(model_id);
```

### 4.5 Metabolic Scores Table

```sql
CREATE TABLE metabolic_scores (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    total_score INTEGER CHECK (total_score BETWEEN 0 AND 100),
    sleep_score INTEGER CHECK (sleep_score BETWEEN 0 AND 30),
    stress_score INTEGER CHECK (stress_score BETWEEN 0 AND 30),
    diet_score INTEGER CHECK (diet_score BETWEEN 0 AND 40),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, date)
);

CREATE INDEX idx_metabolic_scores_user_date ON metabolic_scores(user_id, date);
```

### 4.6 Relationships

- `users` 1:N `health_logs` (One user has many health logs)
- `users` 1:N `simulation_history` (One user has many simulations)
- `users` 1:N `metabolic_scores` (One user has many daily scores)
- `model_performance` tracks aggregate metrics per Bedrock model

---

## 5. API Endpoints Definition

### 5.1 Authentication Endpoints

#### POST /api/register
**Description**: Register a new user

**Request Body**:
```json
{
  "name": "Priya Sharma",
  "email": "priya@example.com",
  "password": "SecurePass123",
  "age": 28,
  "gender": "Female",
  "weight": 65.5,
  "height": 162.0,
  "health_condition": "PCOS",
  "dietary_preference": "Vegetarian"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": { "id": 1, "name": "Priya Sharma", "email": "priya@example.com" },
    "token": "1|abcdef123456..."
  }
}
```

#### POST /api/login
**Description**: Authenticate user and return token

**Request Body**:
```json
{
  "email": "priya@example.com",
  "password": "SecurePass123"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "user": { "id": 1, "name": "Priya Sharma" },
    "token": "2|xyz789..."
  }
}
```

#### POST /api/logout
**Description**: Revoke current token

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 5.2 Simulation Endpoints

#### POST /api/simulate
**Description**: Run metabolic simulation for a scenario

**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "scenario": "2 slices of pizza with cola + high work stress",
  "meal_type": "lunch"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "simulation_id": 42,
    "glucose_risk": "High",
    "glucose_range": "160-200 mg/dL",
    "cortisol_risk": "High",
    "explanation": "Pizza is high in refined carbs and cheese, causing rapid glucose spike. Combined with work stress, cortisol levels will elevate, worsening insulin resistance.",
    "alternatives": [
      "Whole wheat roti with paneer and vegetables",
      "Brown rice with dal and salad",
      "Quinoa bowl with grilled chicken"
    ],
    "model_used": "anthropic.claude-3-5-sonnet-v2",
    "validation_model": "amazon.titan-text-premier-v1",
    "models_agreed": true,
    "response_time_ms": 2847
  }
}
```

**Response** (422 Unprocessable - Blocked by Guardrails):
```json
{
  "success": false,
  "message": "Please consult your healthcare provider for this query",
  "data": {
    "was_blocked": true,
    "reason": "unsafe_medical_advice"
  }
}
```

#### GET /api/simulations
**Description**: Get user's simulation history

**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Results per page (default: 20)
- `from_date` (optional): Filter from date (YYYY-MM-DD)
- `to_date` (optional): Filter to date (YYYY-MM-DD)

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "simulations": [
      {
        "id": 42,
        "input_scenario": "2 slices of pizza with cola + high work stress",
        "glucose_risk": "High",
        "cortisol_risk": "High",
        "created_at": "2026-02-15T14:30:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3,
      "total_results": 58
    }
  }
}
```

### 5.3 Health Log Endpoints

#### POST /api/health-logs
**Description**: Create a new health log entry

**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "log_type": "meal",
  "meal_description": "2 parathas with curd and pickle",
  "meal_type": "breakfast",
  "logged_at": "2026-02-15T08:30:00Z"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Health log created successfully",
  "data": {
    "log": {
      "id": 123,
      "log_type": "meal",
      "meal_description": "2 parathas with curd and pickle",
      "logged_at": "2026-02-15T08:30:00Z"
    }
  }
}
```

#### GET /api/health-logs
**Description**: Get user's health logs

**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `log_type` (optional): Filter by type (meal, sleep, stress, activity, symptom)
- `from_date` (optional): Filter from date
- `to_date` (optional): Filter to date
- `page` (optional): Page number

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": 123,
        "log_type": "meal",
        "meal_description": "2 parathas with curd",
        "logged_at": "2026-02-15T08:30:00Z"
      },
      {
        "id": 122,
        "log_type": "sleep",
        "sleep_hours": 7.5,
        "logged_at": "2026-02-15T06:00:00Z"
      }
    ]
  }
}
```

#### PUT /api/health-logs/{id}
**Description**: Update a health log entry

**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "meal_description": "2 parathas with curd and vegetables",
  "notes": "Felt less bloated today"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Health log updated successfully"
}
```

#### DELETE /api/health-logs/{id}
**Description**: Delete a health log entry

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Health log deleted successfully"
}
```


### 5.4 Metabolic Score Endpoints

#### GET /api/metabolic-score
**Description**: Get current day's metabolic score

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "date": "2026-02-15",
    "total_score": 72,
    "sleep_score": 24,
    "stress_score": 21,
    "diet_score": 27,
    "breakdown": {
      "sleep": { "hours": 7.5, "optimal_range": "7-9 hours" },
      "stress": { "average_level": 4, "optimal_range": "1-3" },
      "diet": { "high_risk_meals": 1, "total_meals": 3 }
    }
  }
}
```

#### GET /api/metabolic-score/history
**Description**: Get metabolic score history

**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `days` (optional): Number of days to retrieve (default: 7, max: 30)

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "scores": [
      { "date": "2026-02-15", "total_score": 72 },
      { "date": "2026-02-14", "total_score": 68 },
      { "date": "2026-02-13", "total_score": 75 }
    ]
  }
}
```

### 5.5 User Profile Endpoints

#### GET /api/profile
**Description**: Get user profile

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Priya Sharma",
      "email": "priya@example.com",
      "age": 28,
      "gender": "Female",
      "weight": 65.5,
      "height": 162.0,
      "health_condition": "PCOS",
      "dietary_preference": "Vegetarian"
    }
  }
}
```

#### PUT /api/profile
**Description**: Update user profile

**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "weight": 64.0,
  "medications": "Metformin 500mg"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

### 5.6 Model Performance Endpoints (Admin/Monitoring)

#### GET /api/admin/model-performance
**Description**: Get performance metrics for all Bedrock models

**Headers**: `Authorization: Bearer {admin_token}`

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "models": [
      {
        "model_id": "anthropic.claude-3-5-sonnet-v2",
        "total_requests": 1523,
        "success_rate": 98.5,
        "avg_response_time_ms": 2341,
        "guardrail_blocks": 12,
        "risk_distribution": {
          "high": 234,
          "medium": 789,
          "low": 500
        },
        "last_used_at": "2026-02-15T14:30:00Z"
      },
      {
        "model_id": "amazon.titan-text-premier-v1",
        "total_requests": 234,
        "success_rate": 97.2,
        "avg_response_time_ms": 1987,
        "guardrail_blocks": 3,
        "risk_distribution": {
          "high": 45,
          "medium": 123,
          "low": 66
        },
        "last_used_at": "2026-02-15T14:25:00Z"
      }
    ],
    "recommended_model": "anthropic.claude-3-5-sonnet-v2"
  }
}
```

---

## 6. Security Considerations

### 6.1 Authentication & Authorization
- **Token-Based Auth**: Laravel Sanctum generates API tokens
- **Token Expiry**: Tokens expire after 24 hours (configurable)
- **Middleware**: All protected routes use `auth:sanctum` middleware
- **Authorization**: Users can only access their own data (enforced via policies)

### 6.2 Data Protection
- **Password Hashing**: bcrypt with cost factor 12
- **Database Encryption**: AES-256 at rest (AWS RDS)
- **TLS**: All API communication over HTTPS (TLS 1.3)
- **Input Validation**: All inputs sanitized and validated
- **SQL Injection Prevention**: Eloquent ORM with parameterized queries

### 6.3 Rate Limiting
- **API Rate Limit**: 60 requests per minute per user
- **Simulation Endpoint**: 10 simulations per minute (to control Bedrock costs)
- **Login Attempts**: Max 5 failed attempts per 15 minutes

### 6.4 AWS Security
- **IAM Roles**: EC2 instance uses IAM role for Bedrock access (no hardcoded keys)
- **Security Groups**: Only ports 80, 443, 22 (SSH) open
- **RDS Access**: Database only accessible from EC2 security group

---

## 7. Performance Optimization

### 7.1 Caching Strategy
- **Redis Cache**: Cache common simulation results for 1 hour
- **Cache Key Format**: `simulation:{user_id}:{hash(scenario)}`
- **Profile Cache**: User profiles cached for 15 minutes
- **Score Cache**: Daily metabolic scores cached until midnight

### 7.2 Database Optimization
- **Indexes**: All foreign keys and frequently queried columns indexed
- **Query Optimization**: Use `select()` to fetch only needed columns
- **Pagination**: All list endpoints paginated (max 100 results per page)

### 7.3 AI Optimization
- **Prompt Caching**: Common prompt templates cached
- **Retry Logic**: Exponential backoff (1s, 2s, 4s) for Bedrock failures
- **Timeout**: 5-second timeout for Bedrock API calls
- **Fallback**: If Bedrock fails, return cached similar prediction

### 7.4 Frontend Optimization
- **Asset Minification**: CSS/JS minified in production
- **Lazy Loading**: Charts load only when visible
- **WebSocket Reconnection**: Auto-reconnect on connection loss

---

## 8. Monitoring & Logging


### 8.1 Application Logging
- **Log Driver**: AWS CloudWatch Logs
- **Log Levels**:
  - `ERROR`: Bedrock failures, database errors
  - `WARNING`: Guardrail blocks, slow queries (>1s)
  - `INFO`: Simulation requests, user registrations
- **Log Retention**: 7 days for hackathon (30 days for production)

### 8.2 Metrics Tracking
- **CloudWatch Metrics**:
  - API response times (p50, p95, p99)
  - Bedrock API latency per model
  - Simulation success rate per model
  - Guardrail block rate per model
  - Model agreement rate (when validation is used)
- **Custom Metrics**:
  - Daily active users
  - Simulations per user per day
  - Average metabolic score
  - Model performance comparison

### 8.3 Error Tracking
- **Laravel Logs**: All exceptions logged with stack traces
- **Bedrock Errors**: API errors logged with request ID for AWS support
- **Database Errors**: Connection failures trigger alerts

---

## 9. Deployment Architecture

### 9.1 AWS Infrastructure

```
┌─────────────────────────────────────────────────────────────┐
│                         AWS Cloud                            │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │  VPC (Virtual Private Cloud)                       │    │
│  │                                                     │    │
│  │  ┌──────────────────┐      ┌──────────────────┐  │    │
│  │  │  Public Subnet   │      │  Private Subnet  │  │    │
│  │  │                  │      │                  │  │    │
│  │  │  ┌────────────┐  │      │  ┌────────────┐ │  │    │
│  │  │  │  EC2       │  │      │  │  RDS       │ │  │    │
│  │  │  │  - Laravel │  │◀─────┼──│  PostgreSQL│ │  │    │
│  │  │  │  - ChromaDB│  │      │  └────────────┘ │  │    │
│  │  │  │  - Nginx   │  │      │                  │  │    │
│  │  │  └────────────┘  │      │                  │  │    │
│  │  │        │         │      │                  │  │    │
│  │  │        ▼         │      │                  │  │    │
│  │  │  Load Balancer   │      │                  │  │    │
│  │  │  (Optional)      │      │                  │  │    │
│  │  └──────────────────┘      └──────────────────┘  │    │
│  │                                                     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Amazon Bedrock (us-east-1)                         │   │
│  │  - Multiple Foundation Models (Auto-Selected)       │   │
│  │  - Guardrails                                       │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  CloudWatch                                          │   │
│  │  - Logs                                              │   │
│  │  - Metrics                                           │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 9.2 EC2 Instance Configuration
- **Instance Type**: t3.medium (2 vCPU, 4GB RAM)
- **OS**: Ubuntu 22.04 LTS
- **Storage**: 30GB SSD
- **Software Stack**:
  - Nginx 1.24
  - PHP 8.2 with FPM
  - Composer 2.x
  - Node.js 20.x (for asset compilation)
  - Docker (for ChromaDB)
  - Supervisor (process manager)

### 9.3 Deployment Process
1. **Code Deployment**: Git pull from repository
2. **Dependencies**: `composer install --optimize-autoloader`
3. **Migrations**: `php artisan migrate --force`
4. **Cache**: `php artisan config:cache && php artisan route:cache`
5. **Assets**: `npm run build`
6. **Services**: Restart PHP-FPM, Nginx, Supervisor

---

## 10. Testing Strategy


### 10.1 Unit Tests
- **Coverage Target**: 60% minimum
- **Test Framework**: PHPUnit
- **Focus Areas**:
  - `SimulationService`: Prompt construction logic
  - `MetabolicScoreService`: Score calculation accuracy
  - `RAGService`: Embedding generation and retrieval

### 10.2 Integration Tests
- **API Endpoints**: Test all endpoints with valid/invalid inputs
- **Database**: Test CRUD operations with transactions
- **Bedrock Mock**: Mock Bedrock responses for predictable testing

### 10.3 Manual Testing
- **Simulation Flow**: End-to-end test from input to result
- **Guardrails**: Test with unsafe queries to verify blocking
- **WebSocket**: Test real-time updates with multiple browser tabs
- **Performance**: Measure response times under load

---

## 11. Cost Estimation (AWS)

### 11.1 Monthly Costs (Hackathon Scale)
- **EC2 t3.medium**: ~$30/month (730 hours)
- **RDS db.t3.micro**: ~$15/month (free tier eligible)
- **Bedrock API**: ~$50/month (estimated 10,000 requests)
- **Data Transfer**: ~$5/month
- **CloudWatch**: ~$5/month
- **Total**: ~$105/month

### 11.2 Cost Optimization
- Use AWS Free Tier where possible
- Cache common simulations to reduce Bedrock calls
- Set Bedrock request limits per user
- Use spot instances for non-critical workloads (if applicable)

---

## 12. Future Enhancements (Post-Hackathon)

### 12.1 Technical Improvements
- **Multi-Region Deployment**: Deploy in ap-south-1 (Mumbai) for lower latency
- **Auto-Scaling**: Add EC2 auto-scaling based on CPU usage
- **CDN**: Use CloudFront for static assets
- **Mobile Apps**: Native iOS/Android apps with push notifications

### 12.2 Feature Enhancements
- **Wearable Integration**: Sync with Fitbit, Apple Watch for real-time glucose data
- **Advanced RAG**: Fine-tune embedding model on Indian food dataset
- **Meal Photo Analysis**: Use AWS Rekognition to analyze food photos
- **Predictive Analytics**: Predict HbA1c trends using historical data

### 12.3 AI Improvements
- **Model Benchmarking**: Continuous A/B testing of available Bedrock models
- **Fine-Tuning**: Fine-tune models on Indian dietary patterns (if supported)
- **Multi-Model Ensemble**: Weighted voting system for predictions
- **Confidence Scores**: Add confidence levels to predictions
- **Model Cost Optimization**: Balance accuracy vs. cost per model

---

## 13. Glossary

- **RAG (Retrieval-Augmented Generation)**: AI technique combining retrieval and generation
- **Embedding**: Vector representation of text for similarity search
- **ChromaDB**: Open-source vector database for embeddings
- **Laravel Reverb**: Laravel's official WebSocket server
- **Bedrock Guardrails**: AWS service for AI safety and content filtering
- **Metabolic Score**: 0-100 score representing daily metabolic health

---

**Document Version**: 1.0  
**Last Updated**: February 15, 2026  
**Author**: HormoneLens Development Team  
**Status**: Ready for Implementation
