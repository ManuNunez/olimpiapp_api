# Database Models & Relationships

This document provides a comprehensive overview of all Eloquent models and their relationships in the Olympic Application API.

## Core Models

### User Model
**File:** `app/Models/User.php`

The User model represents system users with authentication capabilities.

**Properties:**
- `id`: Primary key
- `full_name`: User's full name
- `username`: Unique username
- `email`: User's email address
- `password_hash`: Encrypted password
- `date_of_birth`: User's birth date
- `curp`: Mexican identification number (optional)
- `role_id`: Foreign key to roles table
- `remember_token`: Laravel remember token
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `role()`: BelongsTo - User's primary role
- `roles()`: BelongsToMany - Additional roles (pivot table)
- `students()`: HasMany - Student profiles
- `coaches()`: HasMany - Coach profiles
- `course_enrollments()`: HasMany - Course enrollments
- `certificate_issueds()`: HasMany - Issued certificates
- `messages()`: HasMany - User messages
- `post_comments()`: HasMany - Post comments
- `posts()`: HasMany - Moderated posts

### Student Model
**File:** `app/Models/Student.php`

Represents student profiles with academic information.

**Properties:**
- `id`: Primary key
- `user_id`: Foreign key to users table
- `school_id`: Foreign key to schools table
- `current_grade`: Student's current grade level
- `academic_year`: Current academic year
- `enrollment_number`: Unique student number
- `status`: Student status (active, inactive, graduated)
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `user()`: BelongsTo - Associated user account
- `school()`: BelongsTo - Student's school
- `participations()`: HasMany - Contest participations
- `training_assistances()`: HasMany - Training attendance
- `course_enrollments()`: HasMany - Course enrollments

### Contest Model
**File:** `app/Models/Contest.php`

Represents Olympic contests and competitions.

**Properties:**
- `id`: Primary key
- `name`: Contest name
- `description`: Detailed description
- `start_date`: Contest start date/time
- `end_date`: Contest end date/time
- `registration_deadline`: Registration cutoff
- `max_participants`: Maximum participants
- `status`: Contest status (draft, active, completed)
- `rules`: Contest rules and regulations
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `participations()`: HasMany - Student participations
- `campuses()`: BelongsToMany - Associated campuses
- `classrooms()`: BelongsToMany - Associated classrooms
- `certificates()`: BelongsToMany - Available certificates
- `phase_links()`: HasMany - Phase progression links

### Participation Model
**File:** `app/Models/Participation.php`

Represents student participation in contests.

**Properties:**
- `id`: Primary key
- `student_id`: Foreign key to students table
- `contest_id`: Foreign key to contests table
- `registration_date`: When student registered
- `status`: Participation status
- `score`: Final score (if applicable)
- `rank`: Student's rank in contest
- `submission_time`: When answers were submitted
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `student()`: BelongsTo - Participating student
- `contest()`: BelongsTo - Associated contest
- `answers()`: HasMany - Student's answers

### Campus Model
**File:** `app/Models/Campus.php`

Represents educational institution campuses.

**Properties:**
- `id`: Primary key
- `name`: Campus name
- `address`: Physical address
- `city`: Campus city
- `state`: Campus state/province
- `postal_code`: Postal/zip code
- `phone`: Contact phone number
- `status`: Campus status
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `classrooms()`: HasMany - Campus classrooms
- `contests()`: BelongsToMany - Associated contests
- `schools()`: HasMany - Schools on campus

### Classroom Model
**File:** `app/Models/Classroom.php`

Represents physical or virtual classrooms.

**Properties:**
- `id`: Primary key
- `campus_id`: Foreign key to campuses table
- `name`: Classroom name/number
- `capacity`: Maximum capacity
- `type`: Classroom type (physical, virtual)
- `equipment`: Available equipment
- `status`: Classroom status
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `campus()`: BelongsTo - Associated campus
- `contests()`: BelongsToMany - Associated contests

### Role Model
**File:** `app/Models/Role.php`

Represents user roles and permissions.

**Properties:**
- `id`: Primary key
- `name`: Role name (admin, student, teacher, etc.)
- `description`: Role description
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `users()`: HasMany - Users with this primary role
- `user_roles()`: BelongsToMany - Users with this additional role

## Supporting Models

### School Model
**File:** `app/Models/School.php`

Represents educational institutions.

**Properties:**
- `id`: Primary key
- `name`: School name
- `address`: School address
- `phone`: Contact phone
- `email`: Contact email
- `principal`: Principal's name
- `type`: School type (elementary, middle, high)
- `status`: School status
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `students()`: HasMany - Students enrolled
- `campus()`: BelongsTo - Associated campus

### Certificate Model
**File:** `app/Models/Certificate.php`

Represents certificate templates.

**Properties:**
- `id`: Primary key
- `name`: Certificate name
- `template`: Certificate template
- `description`: Certificate description
- `requirements`: Requirements to earn
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `contests()`: BelongsToMany - Associated contests
- `courses()`: BelongsToMany - Associated courses
- `issued_certificates()`: HasMany - Issued instances

### Course Model
**File:** `app/Models/Course.php`

Represents training courses.

**Properties:**
- `id`: Primary key
- `name`: Course name
- `description`: Course description
- `duration`: Course duration
- `type_id`: Foreign key to course types
- `instructor`: Instructor name
- `start_date`: Course start date
- `end_date`: Course end date
- `status`: Course status
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `type()`: BelongsTo - Course type
- `enrollments()`: HasMany - Student enrollments
- `certificates()`: BelongsToMany - Available certificates

### Training Model
**File:** `app/Models/Training.php`

Represents training sessions.

**Properties:**
- `id`: Primary key
- `course_id`: Foreign key to courses table
- `title`: Training session title
- `description`: Session description
- `date`: Training date
- `duration`: Session duration
- `location`: Training location
- `status`: Session status
- `created_at`, `updated_at`: Timestamps

**Relationships:**
- `course()`: BelongsTo - Associated course
- `assistances()`: HasMany - Attendance records

## Pivot/Junction Models

### UserRole Model
**File:** `app/Models/UserRole.php`

Manages many-to-many relationship between users and roles.

**Properties:**
- `id`: Primary key
- `user_id`: Foreign key to users table
- `role_id`: Foreign key to roles table
- `created_at`, `updated_at`: Timestamps

### ContestCampus Model
**File:** `app/Models/ContestCampus.php`

Manages contest-campus associations.

**Properties:**
- `id`: Primary key
- `contest_id`: Foreign key to contests table
- `campus_id`: Foreign key to campuses table
- `created_at`, `updated_at`: Timestamps

### ContestClassroom Model
**File:** `app/Models/ContestClassroom.php`

Manages contest-classroom associations.

**Properties:**
- `id`: Primary key
- `contest_id`: Foreign key to contests table
- `classroom_id`: Foreign key to classrooms table
- `created_at`, `updated_at`: Timestamps

## Model Relationships Overview

### Primary Relationships

**User-Centric:**
```
User
├── Role (BelongsTo)
├── Roles (BelongsToMany)
├── Student (HasMany)
├── Coach (HasMany)
├── Messages (HasMany)
└── Posts (HasMany)
```

**Student-Centric:**
```
Student
├── User (BelongsTo)
├── School (BelongsTo)
├── Participations (HasMany)
├── TrainingAssistances (HasMany)
└── CourseEnrollments (HasMany)
```

**Contest-Centric:**
```
Contest
├── Participations (HasMany)
├── Campuses (BelongsToMany)
├── Classrooms (BelongsToMany)
├── Certificates (BelongsToMany)
└── PhaseLinks (HasMany)
```

### Complex Relationships

**User → Student → Participation → Contest:**
A user can have multiple student profiles, each can participate in multiple contests.

**Contest → Campus → Classroom:**
Contests can be held at multiple campuses, each campus has multiple classrooms.

**Role System:**
- Direct role assignment: User → Role
- Multiple roles: User → UserRole → Role

## Model Scopes and Accessors

### Common Scopes

**Active Records:**
```php
public function scopeActive($query)
{
    return $query->where('status', 'active');
}
```

**Current Academic Year:**
```php
public function scopeCurrentYear($query)
{
    return $query->where('academic_year', now()->year);
}
```

### Accessors

**Full Name Display:**
```php
public function getFullNameAttribute()
{
    return $this->first_name . ' ' . $this->last_name;
}
```

**Status Badge:**
```php
public function getStatusBadgeAttribute()
{
    return match($this->status) {
        'active' => 'success',
        'inactive' => 'warning',
        'completed' => 'info',
        default => 'secondary'
    };
}
```

## Model Events

### Common Events

**Creating/Created:**
- Generate unique identifiers
- Set default values
- Send notifications

**Updating/Updated:**
- Track changes
- Update related records
- Log activities

**Deleting/Deleted:**
- Clean up related records
- Archive data
- Send notifications

### Example Event Listeners

```php
// User model
protected static function booted()
{
    static::creating(function ($user) {
        $user->username = $user->username ?: $user->generateUsername();
    });
    
    static::created(function ($user) {
        // Send welcome email
        Mail::to($user->email)->send(new WelcomeEmail($user));
    });
}
```

## Model Factories

All models have corresponding factories for testing:

**Location:** `database/factories/`

**Usage:**
```php
// Create a user with student profile
$user = User::factory()
    ->has(Student::factory())
    ->create();

// Create a contest with participations
$contest = Contest::factory()
    ->has(Participation::factory()->count(10))
    ->create();
```

## Best Practices

1. **Use Eloquent Relationships** instead of manual joins
2. **Implement model scopes** for common queries
3. **Use accessors/mutators** for data transformation
4. **Implement model events** for side effects
5. **Use model factories** for testing
6. **Document relationships** clearly
7. **Follow naming conventions** consistently
8. **Use proper foreign key constraints**
9. **Implement soft deletes** where appropriate
10. **Add model validation** rules

---

*For more information on Eloquent models, see the [Laravel Eloquent documentation](https://laravel.com/docs/eloquent).*
