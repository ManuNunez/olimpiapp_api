# API Endpoints Reference

This document provides a comprehensive reference for all API endpoints in the Olympic Application API.

## Base URL

```
Local Development: http://localhost:8000/api
Production: https://api.olimpiapp.com/api
```

## Authentication

Most endpoints require authentication. Include the bearer token in the Authorization header:

```http
Authorization: Bearer {your_token_here}
```

## Common Response Format

All API responses follow a consistent format:

**Success Response:**
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation completed successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "message": "Error description",
    "code": "ERROR_CODE",
    "details": {}
  }
}
```

## Authentication Endpoints

### Register User
```http
POST /auth/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "1|abc123def456..."
}
```

### Login User
```http
POST /auth/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Logout User
```http
POST /auth/logout
```
*Requires authentication*

## Admin Panel Endpoints

All admin endpoints require authentication and admin role.

### Contest Management

#### List Contests
```http
GET /admin/contests
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `search` (optional): Search term
- `status` (optional): Filter by status

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Mathematics Olympiad 2024",
      "description": "Annual mathematics competition",
      "start_date": "2024-05-01T09:00:00.000000Z",
      "end_date": "2024-05-01T12:00:00.000000Z",
      "status": "active",
      "max_participants": 100,
      "current_participants": 45
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25,
    "per_page": 15,
    "last_page": 2
  }
}
```

#### Create Contest
```http
POST /admin/contests
```

**Request Body:**
```json
{
  "name": "Mathematics Olympiad 2024",
  "description": "Annual mathematics competition",
  "start_date": "2024-05-01T09:00:00.000000Z",
  "end_date": "2024-05-01T12:00:00.000000Z",
  "registration_deadline": "2024-04-25T23:59:59.000000Z",
  "max_participants": 100,
  "rules": "Contest rules and regulations...",
  "campus_ids": [1, 2, 3],
  "classroom_ids": [1, 2, 3, 4]
}
```

#### Get Contest
```http
GET /admin/contests/{id}
```

#### Update Contest
```http
PUT /admin/contests/{id}
```

#### Delete Contest
```http
DELETE /admin/contests/{id}
```

#### Search Contests
```http
GET /admin/contests/search?q={search_term}
```

#### Get Contest Statistics
```http
GET /admin/contests/{id}/stats
```

**Response:**
```json
{
  "data": {
    "total_participants": 45,
    "completed_submissions": 40,
    "pending_submissions": 5,
    "average_score": 75.5,
    "completion_rate": 88.9,
    "top_performers": [
      {
        "student_id": 1,
        "name": "Jane Smith",
        "score": 95
      }
    ]
  }
}
```

### Campus Management

#### List Campuses
```http
GET /admin/campuses
```

#### Create Campus
```http
POST /admin/campuses
```

**Request Body:**
```json
{
  "name": "Main Campus",
  "address": "123 Education St",
  "city": "Mexico City",
  "state": "CDMX",
  "postal_code": "12345",
  "phone": "+52 55 1234 5678"
}
```

#### Get Campus
```http
GET /admin/campuses/{id}
```

#### Update Campus
```http
PUT /admin/campuses/{id}
```

#### Delete Campus
```http
DELETE /admin/campuses/{id}
```

### Classroom Management

#### List Classrooms
```http
GET /admin/classrooms
```

#### Create Classroom
```http
POST /admin/classrooms
```

**Request Body:**
```json
{
  "campus_id": 1,
  "name": "Room 101",
  "capacity": 30,
  "type": "physical",
  "equipment": "Projector, Whiteboard, Computers"
}
```

#### Bulk Create Classrooms
```http
POST /admin/classrooms/bulk
```

**Request Body:**
```json
{
  "classrooms": [
    {
      "campus_id": 1,
      "name": "Room 101",
      "capacity": 30,
      "type": "physical"
    },
    {
      "campus_id": 1,
      "name": "Room 102",
      "capacity": 25,
      "type": "physical"
    }
  ]
}
```

#### Get Classrooms by Campus
```http
GET /admin/classrooms/campus/{campusId}
```

#### Get Available Classrooms
```http
GET /admin/classrooms/available/{contestId}
```

## Student Participation Endpoints

All participation endpoints require authentication and student role.

### Contest Participation

#### Get Available Contests
```http
GET /participations/available-contests
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Mathematics Olympiad 2024",
      "description": "Annual mathematics competition",
      "start_date": "2024-05-01T09:00:00.000000Z",
      "end_date": "2024-05-01T12:00:00.000000Z",
      "registration_deadline": "2024-04-25T23:59:59.000000Z",
      "available_slots": 55,
      "can_register": true
    }
  ]
}
```

#### Register for Contest
```http
POST /participations/register
```

**Request Body:**
```json
{
  "contest_id": 1,
  "campus_id": 1,
  "classroom_id": 1
}
```

#### Get My Participations
```http
GET /participations
```

#### Get Participation Details
```http
GET /participations/{id}
```
*Requires resource ownership*

#### Cancel Participation
```http
DELETE /participations/{id}/cancel
```
*Requires resource ownership and contest must not have started*

#### Submit Answers
```http
POST /participations/{participationId}/submit-answers
```
*Requires contest to be active and within time window*

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "answer": "42"
    },
    {
      "question_id": 2,
      "answer": "Paris"
    }
  ]
}
```

### Statistics

#### Get My Statistics
```http
GET /participations/my-stats
```

**Response:**
```json
{
  "data": {
    "total_participations": 5,
    "completed_contests": 3,
    "average_score": 78.5,
    "best_score": 95,
    "certificates_earned": 2,
    "recent_participations": [
      {
        "contest_name": "Mathematics Olympiad 2024",
        "score": 85,
        "rank": 12,
        "date": "2024-05-01"
      }
    ]
  }
}
```

## Contest Information Endpoints

### Contest Details
```http
GET /contests/{contestId}/info
```
*Requires contest access, allowed before and during contest*

### Contest Questions
```http
GET /contests/{contestId}/questions
```
*Requires contest access, only during contest*

### Contest Results
```http
GET /contests/{contestId}/results
```
*Requires contest access, only after contest ends*

### Contest Leaderboard
```http
GET /contests/{contestId}/leaderboard
```
*Available after contest ends*

## Multi-Role Endpoints

### Reports (Admin/Teacher)
```http
GET /reports/contests
```
*Requires admin or teacher role*

```http
GET /reports/students
```
*Requires admin or teacher role*

### Announcements (All authenticated users)
```http
GET /announcements
```

```http
GET /announcements/{id}
```

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "error": {
    "message": "Invalid request data",
    "code": "INVALID_REQUEST",
    "details": {
      "field": "error description"
    }
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "error": {
    "message": "Unauthenticated. Please login first.",
    "code": "UNAUTHENTICATED"
  }
}
```

### 403 Forbidden
```json
{
  "success": false,
  "error": {
    "message": "Unauthorized. Admin access required.",
    "code": "INSUFFICIENT_PERMISSIONS"
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "error": {
    "message": "Resource not found",
    "code": "NOT_FOUND"
  }
}
```

### 422 Validation Error
```json
{
  "success": false,
  "error": {
    "message": "The given data was invalid.",
    "code": "VALIDATION_ERROR",
    "details": {
      "email": ["The email field is required."],
      "password": ["The password must be at least 8 characters."]
    }
  }
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "error": {
    "message": "Too many requests. Please try again later.",
    "code": "RATE_LIMIT_EXCEEDED",
    "details": {
      "retry_after": 60
    }
  }
}
```

## Rate Limiting

The API implements rate limiting to prevent abuse:

- **Authenticated users**: 60 requests per minute
- **Unauthenticated users**: 10 requests per minute
- **Login attempts**: 5 attempts per minute per IP

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Request limit
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Reset time (Unix timestamp)

## Pagination

List endpoints support pagination:

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

**Response Meta:**
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "total": 150,
    "per_page": 15,
    "last_page": 10,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://localhost:8000/api/admin/contests?page=1",
    "last": "http://localhost:8000/api/admin/contests?page=10",
    "prev": null,
    "next": "http://localhost:8000/api/admin/contests?page=2"
  }
}
```

## Filtering and Searching

Many endpoints support filtering and searching:

**Common Parameters:**
- `search`: General search term
- `status`: Filter by status
- `date_from`: Start date filter
- `date_to`: End date filter
- `sort`: Sort field
- `order`: Sort order (asc/desc)

**Example:**
```http
GET /admin/contests?search=mathematics&status=active&sort=start_date&order=desc
```

## CORS Configuration

The API supports CORS for cross-origin requests:

**Allowed Origins:**
- `localhost:3000` (development)
- `localhost:8080` (development)
- Production domains (configured per environment)

**Allowed Methods:**
- GET, POST, PUT, DELETE, OPTIONS

**Allowed Headers:**
- Authorization, Content-Type, Accept, X-Requested-With

---

*For more detailed examples and testing, see the [API Examples documentation](./examples.md).*
