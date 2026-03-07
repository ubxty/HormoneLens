# HormoneLens — API Reference

Complete reference for all REST API endpoints. All API routes are prefixed with `/api`.

---

## Authentication

All authenticated endpoints require:
```
Authorization: Bearer <sanctum-token>
```

Admin endpoints additionally require the authenticated user to have `is_admin = true`.

---

## Response Format

All endpoints return JSON with a consistent structure:

**Success:**
```json
{
  "data": { ... },
  "message": "Optional success message"
}
```

**Paginated:**
```json
{
  "data": [ ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 72 }
}
```

**Error:**
```json
{
  "message": "Error description",
  "errors": { "field": ["Validation message"] }
}
```

---

## 1. Authentication Endpoints

### POST /api/register
**Rate limit:** 10 req/min per IP

| Field | Type | Rules |
|-------|------|-------|
| name | string | required, max:255 |
| email | string | required, email, unique:users |
| password | string | required, min:8, confirmed |
| password_confirmation | string | required |

**Response (201):**
```json
{
  "data": {
    "user": { "id": 1, "name": "Priya", "email": "priya@example.com", "is_admin": false },
    "token": "1|abc123..."
  }
}
```

---

### POST /api/login
**Rate limit:** 10 req/min per IP

| Field | Type | Rules |
|-------|------|-------|
| email | string | required |
| password | string | required |

**Response (200):**
```json
{
  "data": {
    "user": { "id": 1, "name": "Priya", "email": "priya@example.com", "is_admin": false },
    "token": "2|def456..."
  }
}
```

**Error (401):** `{ "message": "Invalid credentials" }`

---

### POST /api/logout
**Auth:** Required

Revokes the current access token.

**Response (200):**
```json
{ "message": "Logged out successfully" }
```

---

## 2. User Profile

### GET /api/user
**Auth:** Required

Returns the authenticated user with loaded relationships.

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Priya",
    "email": "priya@example.com",
    "is_admin": false,
    "health_profile": { ... },
    "disease_data": [ ... ],
    "active_digital_twin": { ... }
  }
}
```

---

## 3. Health Profile

### POST /api/health-profile
**Auth:** Required

Creates the user's health profile. Fails if one already exists.

| Field | Type | Rules |
|-------|------|-------|
| gender | string | required, in:female,male |
| weight | numeric | required, 20–300 |
| height | numeric | required, 50–250 |
| avg_sleep_hours | numeric | required, 0–24 |
| stress_level | string | required, in:low,medium,high |
| physical_activity | string | required, in:sedentary,moderate,active |
| eating_habits | string | nullable |
| water_intake | numeric | required, 0–20 |
| disease_type | string | required |

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "gender": "female",
    "weight": 65,
    "height": 160,
    "avg_sleep_hours": 7,
    "stress_level": "medium",
    "physical_activity": "moderate",
    "eating_habits": "mostly home-cooked",
    "water_intake": 3,
    "disease_type": "diabetes",
    "created_at": "2026-03-07T10:00:00.000000Z",
    "updated_at": "2026-03-07T10:00:00.000000Z"
  }
}
```

---

### GET /api/health-profile
**Auth:** Required

Returns the authenticated user's health profile.

**Response (200):** Same structure as above.

**Error (404):** If no profile exists.

---

### PUT /api/health-profile
**Auth:** Required

Updates the health profile. All fields are optional (partial update).

| Field | Type | Rules |
|-------|------|-------|
| gender | string | sometimes, in:female,male |
| weight | numeric | sometimes, 20–300 |
| height | numeric | sometimes, 50–250 |
| avg_sleep_hours | numeric | sometimes, 0–24 |
| stress_level | string | sometimes, in:low,medium,high |
| physical_activity | string | sometimes, in:sedentary,moderate,active |
| eating_habits | string | sometimes, nullable |
| water_intake | numeric | sometimes, 0–20 |
| disease_type | string | sometimes |

**Response (200):** Updated health profile.

---

## 4. Diseases

### GET /api/diseases
**Auth:** Required

Lists all active diseases with their field definitions.

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "slug": "diabetes",
      "name": "Diabetes",
      "icon": "💉",
      "description": "Type 2 Diabetes risk assessment",
      "fields": [
        {
          "slug": "avg_blood_sugar",
          "label": "Average Blood Sugar",
          "field_type": "number",
          "category": "vitals",
          "options": null,
          "is_required": true,
          "sort_order": 1
        },
        {
          "slug": "family_history",
          "label": "Family History of Diabetes",
          "field_type": "boolean",
          "category": "history",
          "options": null,
          "is_required": true,
          "sort_order": 2
        }
      ]
    }
  ]
}
```

---

### GET /api/diseases/{slug}
**Auth:** Required

Returns disease definition + the authenticated user's saved data for that disease.

**Response (200):**
```json
{
  "data": {
    "disease": {
      "id": 1,
      "slug": "diabetes",
      "name": "Diabetes",
      "fields": [ ... ]
    },
    "user_data": {
      "avg_blood_sugar": 150,
      "family_history": true,
      "frequent_urination": "often"
    }
  }
}
```

---

### POST /api/diseases/{slug}
**Auth:** Required

Saves or updates the user's data for a disease.

| Field | Type | Rules |
|-------|------|-------|
| field_values | object | required, keys must match disease field slugs |

**Example Body:**
```json
{
  "field_values": {
    "avg_blood_sugar": 150,
    "family_history": true,
    "frequent_urination": "often",
    "excessive_thirst": "occasionally",
    "fatigue": "often",
    "blurred_vision": false,
    "sugar_cravings": "frequent"
  }
}
```

**Response (200):**
```json
{
  "message": "Disease data saved successfully",
  "data": { "avg_blood_sugar": 150, "family_history": true, ... }
}
```

---

### GET /api/diseases/my-data
**Auth:** Required

Returns all disease data for the authenticated user.

**Response (200):**
```json
{
  "data": [
    {
      "disease": { "slug": "diabetes", "name": "Diabetes" },
      "field_values": { "avg_blood_sugar": 150, ... }
    }
  ]
}
```

---

## 5. Digital Twin

### POST /api/digital-twin/generate
**Auth:** Required

Generates (or regenerates) the user's digital twin. Deactivates previous twins.

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "metabolic_health_score": 62.5,
    "insulin_resistance_score": 45.0,
    "sleep_score": 70.0,
    "stress_score": 55.0,
    "diet_score": 68.0,
    "overall_risk_score": 55.25,
    "risk_category": "moderate",
    "is_active": true,
    "created_at": "2026-03-07T10:00:00.000000Z"
  }
}
```

---

### GET /api/digital-twin/active
**Auth:** Required

Returns the currently active digital twin.

**Response (200):** Same structure as above.

**Error (404):** If no active twin exists.

---

### GET /api/digital-twin
**Auth:** Required

Returns all digital twin snapshots for the user (paginated).

---

### GET /api/digital-twin/{id}
**Auth:** Required (ownership check)

Returns a specific digital twin by ID.

---

## 6. Simulations

### POST /api/simulations/run
**Auth:** Required

Runs a lifestyle simulation.

| Field | Type | Rules |
|-------|------|-------|
| type | string | required, in:meal,sleep,stress |
| description | string | required, max:500 |
| parameters | object | optional |
| parameters.sleep_hours | numeric | for sleep type |
| parameters.stress_level | string | for stress type, in:low,medium,high |

**Example Body (sleep):**
```json
{
  "type": "sleep",
  "description": "What if I sleep 8 hours per night?",
  "parameters": { "sleep_hours": 8 }
}
```

**Example Body (stress):**
```json
{
  "type": "stress",
  "description": "What if I reduce my stress to low?",
  "parameters": { "stress_level": "low" }
}
```

**Example Body (meal):**
```json
{
  "type": "meal",
  "description": "What if I reduce sugar intake?"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "type": "sleep",
    "input_data": { "type": "sleep", "description": "...", "parameters": { "sleep_hours": 8 } },
    "original_risk_score": 55.25,
    "simulated_risk_score": 48.10,
    "risk_change": -7.15,
    "risk_category_before": "moderate",
    "risk_category_after": "moderate",
    "rag_explanation": "Improving sleep to 8 hours can significantly reduce metabolic stress...",
    "rag_confidence": 0.82,
    "results": { ... },
    "alerts": [],
    "created_at": "2026-03-07T10:05:00.000000Z"
  }
}
```

**Error:** `RuntimeException` if no active digital twin exists.

---

### GET /api/simulations
**Auth:** Required

Paginated list of user's simulations.

| Param | Type | Default |
|-------|------|---------|
| per_page | integer | 15 |

---

### GET /api/simulations/{id}
**Auth:** Required (ownership check)

Returns a single simulation with full details.

---

## 7. Food Impact

### POST /api/food-impact
**Auth:** Required

Simulates the glycemic impact of a specific food.

| Field | Type | Rules |
|-------|------|-------|
| food_item | string | required, max:255 |
| quantity | string | nullable |

**Example Body:**
```json
{
  "food_item": "white rice",
  "quantity": "1 cup"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 5,
    "type": "food_impact",
    "input_data": { "food_item": "white rice", "quantity": "1 cup" },
    "original_risk_score": 55.25,
    "simulated_risk_score": 60.10,
    "risk_change": 4.85,
    "risk_category_before": "moderate",
    "risk_category_after": "high",
    "rag_explanation": "White rice has a high glycemic index...",
    "results": {
      "alternatives": ["brown rice", "quinoa", "cauliflower rice"]
    },
    "alerts": [
      { "type": "high_gi", "title": "High Glycemic Food Detected", "severity": "warning" }
    ]
  }
}
```

---

## 8. Alerts

### GET /api/alerts
**Auth:** Required

| Param | Type | Default |
|-------|------|---------|
| per_page | integer | 20 |

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "type": "risk_threshold",
      "title": "High Risk Alert",
      "message": "Your simulated risk score exceeded the critical threshold of 75.",
      "severity": "critical",
      "is_read": false,
      "simulation_id": 3,
      "created_at": "2026-03-07T10:05:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "total": 5 }
}
```

---

### GET /api/alerts/unread-count
**Auth:** Required

**Response (200):**
```json
{ "data": { "count": 3 } }
```

---

### PATCH /api/alerts/{id}/read
**Auth:** Required

Marks a single alert as read.

**Response (200):**
```json
{ "message": "Alert marked as read" }
```

---

### PATCH /api/alerts/read-all
**Auth:** Required

Marks all unread alerts as read for the authenticated user.

**Response (200):**
```json
{ "message": "All alerts marked as read" }
```

---

## 9. Simulation History

### GET /api/history
**Auth:** Required

| Param | Type | Description |
|-------|------|-------------|
| type | string | Filter by simulation type (meal/sleep/stress/food_impact) |
| date_from | date | Start date (YYYY-MM-DD) |
| date_to | date | End date (YYYY-MM-DD) |
| per_page | integer | Items per page (default 15) |

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "type": "sleep",
      "input_data": { ... },
      "original_risk_score": 55.25,
      "simulated_risk_score": 48.10,
      "risk_change": -7.15,
      "risk_category_before": "moderate",
      "risk_category_after": "moderate",
      "rag_explanation": "...",
      "results": { ... },
      "alerts_count": 0,
      "created_at": "2026-03-07T10:05:00.000000Z"
    }
  ]
}
```

---

### GET /api/history/{id}
**Auth:** Required (ownership check)

Returns full simulation details.

---

### POST /api/history/{id}/rerun
**Auth:** Required (ownership check)

Re-runs a previous simulation with its original parameters against the current digital twin.

**Response (201):** New SimulationResource.

---

### DELETE /api/history/{id}
**Auth:** Required (ownership check)

Deletes a simulation history entry.

**Response (204):** No content.

---

## 10. RAG Knowledge Query

### POST /api/rag/query
**Auth:** Required  
**Rate limit:** 20 req/min

| Field | Type | Rules |
|-------|------|-------|
| question | string | required, max:500 |
| disease_context | string | nullable, in:diabetes,pcod |

**Example Body:**
```json
{
  "question": "What foods help manage insulin resistance?",
  "disease_context": "diabetes"
}
```

**Response (200):**
```json
{
  "data": {
    "answer": "Foods that help manage insulin resistance include high-fiber foods like whole grains, legumes, and vegetables. Cinnamon has been shown to improve insulin sensitivity...",
    "reasoning_path": [
      "Diabetes Knowledge Base",
      "Insulin Resistance",
      "Diet & Insulin Sensitivity"
    ],
    "source_nodes": [
      { "id": 12, "title": "Diet & Insulin Sensitivity" }
    ],
    "source_pages": [
      { "id": 24, "page_number": 1, "node_id": 12 }
    ],
    "confidence": 0.85
  }
}
```

---

## 11. Admin Endpoints

All admin endpoints require `Authorization: Bearer <token>` with an admin user.

### GET /api/admin/dashboard

**Response (200):**
```json
{
  "data": {
    "total_users": 150,
    "new_users_7d": 12,
    "simulations": {
      "total": 450,
      "today": 15,
      "week": 87
    },
    "avg_risk_score": 48.5,
    "risk_distribution": {
      "low": 45,
      "moderate": 62,
      "high": 30,
      "critical": 13
    },
    "unread_alerts": 23
  }
}
```

---

### GET /api/admin/users

| Param | Type | Description |
|-------|------|-------------|
| search | string | Search by name or email |
| is_admin | boolean | Filter admins/non-admins |
| per_page | integer | default 15 |

---

### GET /api/admin/users/{id}

Returns user with health_profile, disease_data, active_digital_twin, and recent simulations (10).

---

### PATCH /api/admin/users/{id}/toggle-admin

Toggles the `is_admin` flag. Cannot toggle self.

**Response (200):** Updated UserResource.

---

### GET /api/admin/simulations

| Param | Type | Description |
|-------|------|-------------|
| type | string | meal/sleep/stress/food_impact |
| user_id | integer | Filter by user |
| date_from | date | YYYY-MM-DD |
| date_to | date | YYYY-MM-DD |
| search | string | Search in description |
| per_page | integer | default 15 |

---

### GET /api/admin/simulations/{id}

Returns simulation with user and alerts loaded.

---

### GET /api/admin/alerts

| Param | Type | Description |
|-------|------|-------------|
| severity | string | info/warning/critical |
| type | string | risk_threshold/high_gi/low_sleep/high_stress/repeated_risk |
| user_id | integer | Filter by user |
| is_read | boolean | Filter by read status |
| date_from | date | YYYY-MM-DD |
| date_to | date | YYYY-MM-DD |
| search | string | Search in title/message |
| per_page | integer | default 20 |

---

### GET /api/admin/alerts/{id}

Returns alert with user info.

---

### GET /api/admin/reports

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| period_days | integer | 30 | Time window in days |

**Response (200):**
```json
{
  "data": {
    "period": {
      "days": 30,
      "start": "2026-02-05",
      "end": "2026-03-07"
    },
    "new_users": 15,
    "simulations": 120,
    "risk_distribution": { "low": 12, "moderate": 8, "high": 5, "critical": 2 },
    "daily_risk_scores": [
      { "date": "2026-02-05", "avg_risk_score": 45.2 },
      { "date": "2026-02-06", "avg_risk_score": 47.1 }
    ],
    "daily_simulations": [
      { "date": "2026-02-05", "count": 4 },
      { "date": "2026-02-06", "count": 6 }
    ],
    "daily_alerts_by_severity": [
      { "date": "2026-02-05", "info": 0, "warning": 2, "critical": 1 }
    ]
  }
}
```

---

## 12. Admin — RAG Management

### Documents

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/admin/rag/documents | List all documents (with node/page counts) |
| POST | /api/admin/rag/documents | Create document (title required, description optional) |
| GET | /api/admin/rag/documents/{id} | Show document with full node tree |
| PUT | /api/admin/rag/documents/{id} | Update document |
| DELETE | /api/admin/rag/documents/{id} | Delete document (cascading) |

### Nodes

| Method | Endpoint | Body |
|--------|----------|------|
| POST | /api/admin/rag/nodes | `{ document_id, parent_id?, title, summary?, keywords }` |
| PUT | /api/admin/rag/nodes/{id} | `{ title?, keywords? }` |
| DELETE | /api/admin/rag/nodes/{id} | Recursive delete (children + pages) |

### Pages

| Method | Endpoint | Body |
|--------|----------|------|
| POST | /api/admin/rag/pages | `{ node_id, page_number, content }` |
| PUT | /api/admin/rag/pages/{id} | `{ content?, page_number? }` |
| DELETE | /api/admin/rag/pages/{id} | — |

---

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Resource created |
| 204 | Deleted (no content) |
| 401 | Unauthenticated |
| 403 | Forbidden (not admin) |
| 404 | Resource not found |
| 422 | Validation error |
| 429 | Rate limit exceeded |
| 500 | Server error |
