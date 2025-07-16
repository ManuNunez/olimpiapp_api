# Middleware Documentation

This document explains the custom middlewares created for the Olympic Application API and how to use them.

## Available Middlewares

### 1. Admin Middleware (`admin`)
**File:** `app/Http/Middleware/Admin.php`
**Purpose:** Ensures only users with admin role can access protected routes.

**Usage:**
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
```

**Checks:**
- User is authenticated
- User has `admin` role (either via direct role or roles relationship)

### 2. Student Middleware (`student`)
**File:** `app/Http/Middleware/Student.php`
**Purpose:** Ensures only users with student role and student profile can access protected routes.

**Usage:**
```php
Route::middleware(['auth:sanctum', 'student'])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
});
```

**Checks:**
- User is authenticated
- User has `student` role (either via direct role or roles relationship)
- User has a student profile in the database

### 3. Role Checker Middleware (`role`)
**File:** `app/Http/Middleware/RoleChecker.php`
**Purpose:** Flexible role-based access control for multiple roles.

**Usage:**
```php
// Single role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/reports', [ReportController::class, 'index']);
});

// Multiple roles
Route::middleware(['auth:sanctum', 'role:admin,teacher,coordinator'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
});
```

**Features:**
- Accepts multiple roles separated by commas
- Case-insensitive role matching
- Supports both direct role and roles relationship

### 4. Contest Access Middleware (`contest.access`)
**File:** `app/Http/Middleware/ContestAccess.php`
**Purpose:** Validates that a student has access to a specific contest.

**Usage:**
```php
Route::middleware(['auth:sanctum', 'student', 'contest.access'])->group(function () {
    Route::get('/contests/{contestId}/questions', [ContestController::class, 'getQuestions']);
});
```

**Checks:**
- User is authenticated
- User has student profile
- Contest exists
- Student has participation in the contest

**Adds to Request:**
- `contest` - Contest model instance
- `participation` - Participation model instance
- `student` - Student model instance

### 5. Contest Time Window Middleware (`contest.time`)
**File:** `app/Http/Middleware/ContestTimeWindow.php`
**Purpose:** Restricts access based on contest timing.

**Usage:**
```php
// Only before contest starts
Route::middleware(['contest.access', 'contest.time:before'])->group(function () {
    Route::post('/contests/{contestId}/register', [ParticipationController::class, 'register']);
});

// Only during contest
Route::middleware(['contest.access', 'contest.time:during'])->group(function () {
    Route::post('/contests/{contestId}/submit', [ParticipationController::class, 'submit']);
});

// Only after contest ends
Route::middleware(['contest.access', 'contest.time:after'])->group(function () {
    Route::get('/contests/{contestId}/results', [ParticipationController::class, 'results']);
});
```

**Time Windows:**
- `before` - Only before contest starts
- `during` - Only during contest period
- `after` - Only after contest ends
- `not_during` - Before or after, but not during contest
- `before_or_during` - Before or during, but not after contest

### 6. Resource Owner Middleware (`resource.owner`)
**File:** `app/Http/Middleware/ResourceOwner.php`
**Purpose:** Ensures users can only access their own resources.

**Usage:**
```php
Route::middleware(['auth:sanctum', 'student', 'resource.owner:participation'])->group(function () {
    Route::get('/my-participations/{id}', [ParticipationController::class, 'show']);
});
```

**Resource Types:**
- `participation` - Validates participation ownership

**Adds to Request:**
- `participation` - Participation model instance (for participation resources)
- `student` - Student model instance

## Controller Integration

### Admin Panel Controllers
All admin panel controllers should use the admin middleware:

```php
class ContestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }
}
```

### Student Controllers
Student-facing controllers should use the student middleware:

```php
class ParticipationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'student']);
    }
}
```

## Route Examples

### Admin Routes
```php
Route::prefix('admin')->group(function () {
    Route::controller(ContestController::class)->group(function () {
        Route::get('/contests', 'ListContests');
        Route::post('/contests', 'CreateContest');
        Route::get('/contests/{id}', 'GetContest');
        Route::put('/contests/{id}', 'UpdateContest');
        Route::delete('/contests/{id}', 'DeleteContest');
    });
});
```

### Student Participation Routes
```php
Route::prefix('participations')->controller(ParticipationController::class)->group(function () {
    // Basic participation actions
    Route::get('/available-contests', 'GetAvailableContests');
    Route::post('/register', 'RegisterForContest');
    Route::get('/', 'GetMyParticipations');
    
    // Resource ownership required
    Route::middleware(['resource.owner:participation'])->group(function () {
        Route::get('/{id}', 'GetParticipation');
        Route::delete('/{id}/cancel', 'CancelParticipation')
            ->middleware('contest.access', 'contest.time:before');
    });
    
    // Time-restricted actions
    Route::middleware(['contest.access'])->group(function () {
        Route::post('/{participationId}/submit-answers', 'SubmitAnswers')
            ->middleware('contest.time:during');
    });
});
```

### Complex Middleware Combinations
```php
// Multiple role access
Route::middleware(['auth:sanctum', 'role:admin,teacher,coordinator'])->group(function () {
    Route::get('/reports/contests', [ReportController::class, 'contestReports']);
});

// Contest time windows with ownership
Route::middleware(['auth:sanctum', 'student', 'contest.access', 'resource.owner:participation'])
    ->group(function () {
        Route::get('/my-participations/{id}/answers', [ParticipationController::class, 'getMyAnswers'])
            ->middleware('contest.time:after');
    });
```

## Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated. Please login first."
}
```

### 403 Forbidden
```json
{
    "message": "Unauthorized. Admin access required."
}
```

### 404 Not Found
```json
{
    "message": "Student profile not found. Please contact administrator."
}
```

### 403 Time Restriction
```json
{
    "message": "This action is not allowed after the contest has ended.",
    "contest_start": "2024-08-15T10:00:00.000000Z",
    "contest_end": "2024-08-15T13:00:00.000000Z",
    "current_time": "2024-08-15T14:00:00.000000Z"
}
```

## Testing Middleware

### Testing Admin Access
```bash
# With admin token
curl -X GET "http://localhost:8000/api/admin/contests" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"

# With student token (should fail)
curl -X GET "http://localhost:8000/api/admin/contests" \
  -H "Authorization: Bearer {student_token}" \
  -H "Accept: application/json"
```

### Testing Student Access
```bash
# With student token
curl -X GET "http://localhost:8000/api/participations" \
  -H "Authorization: Bearer {student_token}" \
  -H "Accept: application/json"

# With admin token (should fail)
curl -X GET "http://localhost:8000/api/participations" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

## Database Structure Requirements

### Roles Table
```sql
CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Required roles
INSERT INTO roles (name, description) VALUES 
('admin', 'Administrator with full access'),
('student', 'Student participant'),
('teacher', 'Teacher/Instructor'),
('coordinator', 'Program coordinator');
```

### User Roles
The middleware supports two ways of assigning roles:

1. **Direct Role Assignment** (via `role_id` in users table)
2. **Many-to-Many Relationship** (via `user_roles` pivot table)

Both methods are supported and the middleware will check both.

## Installation Summary

1. **Middleware Files Created:**
   - `app/Http/Middleware/Admin.php`
   - `app/Http/Middleware/Student.php`
   - `app/Http/Middleware/RoleChecker.php`
   - `app/Http/Middleware/ContestAccess.php`
   - `app/Http/Middleware/ContestTimeWindow.php`
   - `app/Http/Middleware/ResourceOwner.php`

2. **Bootstrap Configuration:**
   Updated `bootstrap/app.php` to register middleware aliases.

3. **Controller Integration:**
   Updated admin panel controllers to use admin middleware.
   Updated participation controller to use student middleware.

The middleware system is now fully functional and provides comprehensive access control for your Olympic Application API.
