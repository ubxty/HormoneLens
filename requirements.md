# HormoneLens - Requirements Document

## 1. Project Overview

### 1.1 Project Name
HormoneLens

### 1.2 Project Description
HormoneLens is an AI-powered Metabolic Simulator designed for the AWS AI for Bharat Hackathon. Unlike traditional health tracking apps that only log past events, HormoneLens uses Generative AI to simulate and predict the metabolic impact of food choices and activities before users act on them. The system targets Indian users managing PCOS (Polycystic Ovary Syndrome) and Diabetes by providing personalized, context-aware predictions of glucose and cortisol responses.

### 1.3 Problem Statement
Indian users with PCOS and Diabetes struggle to make informed dietary and lifestyle decisions because:
- Traditional apps only track past events, offering no predictive guidance
- Generic nutrition advice doesn't account for individual metabolic responses
- Users lack real-time feedback on how specific foods or stress will affect their hormones
- Medical advice is often inaccessible or not personalized to Indian dietary patterns

### 1.4 Solution
HormoneLens provides a predictive metabolic simulation engine that:
- Predicts glucose and cortisol spikes before consumption/activity
- Personalizes predictions using historical health data via RAG (Retrieval-Augmented Generation)
- Ensures safety through AWS Bedrock Guardrails
- Visualizes metabolic impact through an intuitive dashboard
- Calculates a dynamic daily Metabolic Score (0-100)

### 1.5 Target Hackathon
AWS AI for Bharat Hackathon

---

## 2. User Personas

### 2.1 Persona 1: PCOS Patient (Priya)
- **Age**: 28 years
- **Location**: Bangalore, India
- **Condition**: PCOS with insulin resistance
- **Goals**:
  - Manage weight and hormonal balance
  - Avoid foods that trigger inflammation or insulin spikes
  - Understand how stress affects cortisol levels
- **Pain Points**:
  - Dairy products cause bloating but unsure about alternatives
  - Irregular periods make it hard to track patterns
  - Needs guidance on Indian meals (dosa, paratha, biryani)
- **Tech Savviness**: Moderate (uses smartphone apps daily)

### 2.2 Persona 2: Diabetic User (Rajesh)
- **Age**: 45 years
- **Location**: Delhi, India
- **Condition**: Type 2 Diabetes
- **Goals**:
  - Keep blood glucose levels stable throughout the day
  - Make informed meal choices at restaurants and home
  - Reduce HbA1c levels over time
- **Pain Points**:
  - Uncertain about portion sizes for Indian staples (rice, roti)
  - Experiences unexpected glucose spikes despite "healthy" choices
  - Wants to enjoy occasional sweets without severe consequences
- **Tech Savviness**: Low to Moderate (prefers simple interfaces)

---

## 3. Functional Requirements

### 3.1 User Authentication & Profile Management

#### 3.1.1 User Registration
- Users must be able to register using email and password
- Registration must collect basic health profile:
  - Age, gender, weight, height
  - Health condition (PCOS, Diabetes, Both, Other)
  - Current medications (optional)
  - Dietary preferences (vegetarian, non-vegetarian, vegan)
- System must validate email format and password strength (min 8 characters)

#### 3.1.2 User Login
- Users must be able to log in with email and password
- System must support session management with secure tokens
- Failed login attempts must be logged for security

#### 3.1.3 Profile Updates
- Users must be able to update their health profile at any time
- Changes to health conditions must trigger a re-calibration of the Metabolic Score baseline

### 3.2 Predictive Metabolic Simulation

#### 3.2.1 Meal/Activity Input
- Users must be able to input:
  - Meal description (text or voice input): e.g., "2 parathas with curd"
  - Activity description: e.g., "30 minutes of yoga"
  - Stress event: e.g., "work deadline in 2 hours"
- System must support Indian food names and regional variations
- Input must accept natural language descriptions

#### 3.2.2 AI-Powered Prediction
- System must use Amazon Bedrock (Claude 3.5 Sonnet) to generate predictions
- Predictions must include:
  - **Glucose Impact**: High/Medium/Low risk with estimated spike range (mg/dL)
  - **Cortisol Impact**: High/Medium/Low risk with stress level indicator
  - **Explanation**: Brief reasoning for the prediction (2-3 sentences)
  - **Alternatives**: 2-3 healthier alternatives if risk is High
- Predictions must be generated within 3 seconds

#### 3.2.3 Context-Aware Personalization (RAG)
- System must retrieve user's historical health logs before generating predictions
- RAG system must consider:
  - Past meal reactions (e.g., "Dairy causes bloating for this user")
  - Sleep patterns from last 7 days
  - Recent stress events
  - Time of day and meal timing
- Retrieved context must be embedded into the AI prompt for personalization

#### 3.2.4 Safety Guardrails
- System must use AWS Bedrock Guardrails to filter:
  - Medical advice that contradicts professional guidance
  - Dangerous dietary recommendations (e.g., extreme fasting)
  - Misinformation about medications
- Blocked content must return a safe fallback message: "Please consult your healthcare provider for this query"

### 3.3 Health History & Logging

#### 3.3.1 Manual Logging
- Users must be able to log actual meals consumed with timestamps
- Users must be able to log:
  - Sleep duration (hours)
  - Stress level (1-10 scale)
  - Physical activity (type and duration)
  - Symptoms (bloating, fatigue, mood swings)

#### 3.3.2 Historical Data Retrieval
- System must store all logs in PostgreSQL with encryption at rest
- Users must be able to view past logs filtered by:
  - Date range
  - Meal type (breakfast, lunch, dinner, snacks)
  - Symptom type
- Historical data must be vectorized and stored in ChromaDB for RAG retrieval

### 3.4 Metabolic Score

#### 3.4.1 Score Calculation
- System must calculate a daily Metabolic Score (0-100) based on:
  - **Sleep Quality** (30% weight): 7-9 hours = optimal
  - **Stress Level** (30% weight): Lower stress = higher score
  - **Diet Quality** (40% weight): Low-risk meals = higher score
- Score must update in real-time as user logs data

#### 3.4.2 Score Visualization
- Dashboard must display:
  - Current day's score with color coding (Red: 0-40, Yellow: 41-70, Green: 71-100)
  - 7-day score trend graph
  - Breakdown of score components (sleep, stress, diet)

### 3.5 Dashboard & Visualization

#### 3.5.1 Real-Time Spike Visualization
- Dashboard must display predicted glucose/cortisol spikes as:
  - Line graph showing projected levels over next 2-4 hours
  - Color-coded risk zones (green, yellow, red)
- Visualization must update immediately after simulation

#### 3.5.2 Daily Summary
- Dashboard must show:
  - Total meals logged today
  - Average risk level of consumed meals
  - Current Metabolic Score
  - Recommendations for remaining meals

#### 3.5.3 Insights & Trends
- System must generate weekly insights:
  - "You had 3 high-risk meals this week"
  - "Your sleep improved by 15% compared to last week"
  - "Avoiding dairy reduced bloating incidents by 50%"

---

## 4. Non-Functional Requirements

### 4.1 Performance

#### 4.1.1 Response Time
- AI simulation results must appear within 3 seconds of user input
- Dashboard must load within 2 seconds
- Historical data queries must return results within 1 second

#### 4.1.2 Scalability
- System must support at least 1,000 concurrent users during hackathon demo
- Database must handle 10,000+ health log entries per user

#### 4.1.3 AI Model Performance
- Bedrock API calls must have retry logic with exponential backoff
- System must cache common meal predictions to reduce API costs

### 4.2 Security

#### 4.2.1 Data Encryption
- All health data must be encrypted at rest in AWS RDS (AES-256)
- Data in transit must use TLS 1.3
- User passwords must be hashed using bcrypt (cost factor 12)

#### 4.2.2 Access Control
- Users must only access their own health data
- API endpoints must require authentication tokens (JWT)
- Session tokens must expire after 24 hours

#### 4.2.3 Compliance
- System must comply with basic data privacy principles (no PHI sharing)
- User data must not be used to train AI models without explicit consent

### 4.3 Reliability

#### 4.3.1 Availability
- System must maintain 95% uptime during hackathon evaluation period
- Database backups must run daily

#### 4.3.2 Error Handling
- AI failures must return graceful error messages (no stack traces to users)
- System must log all errors to CloudWatch for debugging
- Critical failures (DB connection loss) must trigger alerts

#### 4.3.3 Data Integrity
- All health logs must be validated before storage (e.g., sleep hours 0-24)
- Duplicate entries within 1-minute window must be prevented

### 4.4 Usability

#### 4.4.1 User Interface
- UI must be mobile-responsive (primary target: smartphones)
- Text must be readable (minimum 14px font size)
- Color contrast must meet WCAG AA standards

#### 4.4.2 Language Support
- System must support English (primary language for hackathon)
- Food names must recognize Hindi/regional transliterations (e.g., "dahi" = "curd")

#### 4.4.3 Accessibility
- Forms must have proper labels for screen readers
- Error messages must be clear and actionable

### 4.5 Maintainability

#### 4.5.1 Code Quality
- Code must follow Laravel best practices (PSR-12 standards)
- All API endpoints must have inline documentation
- Critical functions must have unit tests (minimum 60% coverage)

#### 4.5.2 Monitoring
- System must log all AI predictions for audit trail
- API response times must be tracked in CloudWatch

---

## 5. Tech Stack

### 5.1 Backend Framework
- **Laravel 10.x** (PHP 8.2+)
  - RESTful API architecture
  - Eloquent ORM for database interactions
  - Laravel Sanctum for API authentication

### 5.2 AI & Machine Learning
- **Amazon Bedrock** (Claude 3.5 Sonnet)
  - Primary AI model for metabolic predictions
  - Bedrock Guardrails for safety filtering
- **ChromaDB**
  - Vector database for storing health log embeddings
  - Enables context-aware RAG retrieval

### 5.3 Database
- **PostgreSQL 15+** (AWS RDS)
  - Primary relational database for user profiles and health logs
  - Encryption at rest enabled
  - Automated backups configured

### 5.4 Frontend
- Blade
  - Single Page Application (SPA) for dashboard
  - Chart.js or Recharts for data visualization
  - Responsive design using Tailwind CSS

### 5.5 Infrastructure (AWS)
- **AWS EC2** or **AWS Elastic Beanstalk**: Application hosting
- **AWS RDS**: Managed PostgreSQL database
- **AWS S3**: Static asset storage (if needed)
- **AWS CloudWatch**: Logging and monitoring
- **AWS Secrets Manager**: Secure storage of API keys and credentials

### 5.6 Development Tools
- **Git**: Version control
- **Postman**: API testing
- **Docker**: Local development environment (optional)

---

## 6. Acceptance Criteria

### 6.1 Minimum Viable Product (MVP) for Hackathon
The following features must be fully functional for hackathon submission:

1. User registration and login working
2. Meal input accepts natural language (Indian foods recognized)
3. AI prediction returns glucose/cortisol risk within 3 seconds
4. Predictions are personalized using at least 3 historical data points
5. Metabolic Score calculates correctly based on sleep, stress, diet
6. Dashboard displays current score and predicted spikes
7. AWS Bedrock Guardrails block at least one unsafe query type
8. All health data encrypted at rest in RDS

### 6.2 Success Metrics
- **User Engagement**: Users simulate at least 3 meals during demo
- **Prediction Accuracy**: AI explanations are coherent and relevant
- **Performance**: 95% of predictions return within 3 seconds
- **Safety**: Zero unsafe medical advice reaches users

---

## 7. Out of Scope (Post-Hackathon Features)

The following features are not required for the hackathon MVP but may be added later:

- Integration with wearable devices (Fitbit, Apple Watch)
- Multi-language support (Hindi, Tamil, Telugu)
- Social features (sharing meals with friends)
- Nutritionist consultation booking
- Advanced analytics (HbA1c prediction, PCOS symptom tracking)
- Mobile native apps (iOS/Android)

---

## 8. Risks & Mitigation

### 8.1 Technical Risks
- **Risk**: Bedrock API rate limits during demo
  - **Mitigation**: Implement caching for common queries; use exponential backoff
- **Risk**: RAG retrieval returns irrelevant context
  - **Mitigation**: Fine-tune embedding model; limit retrieval to top 5 results
- **Risk**: Database performance degrades with large datasets
  - **Mitigation**: Index frequently queried columns; use pagination

### 8.2 Timeline Risks
- **Risk**: Hackathon deadline is tight (assume 48-72 hours)
  - **Mitigation**: Prioritize MVP features; defer nice-to-have features
- **Risk**: AWS setup delays
  - **Mitigation**: Use AWS free tier; prepare infrastructure templates in advance

---

## 9. Glossary

- **RAG (Retrieval-Augmented Generation)**: AI technique that retrieves relevant context before generating responses
- **Metabolic Score**: A 0-100 score representing overall metabolic health based on sleep, stress, and diet
- **Guardrails**: AWS Bedrock feature that filters unsafe or inappropriate AI outputs
- **PCOS**: Polycystic Ovary Syndrome, a hormonal disorder affecting women
- **HbA1c**: Hemoglobin A1c, a measure of average blood glucose over 3 months

---

**Document Version**: 1.0  
**Last Updated**: February 15, 2026  
**Author**: HormoneLens Team
