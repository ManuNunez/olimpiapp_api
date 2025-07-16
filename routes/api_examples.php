<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\admin_panel\ContestController;
use App\Http\Controllers\admin_panel\CampusController;
use App\Http\Controllers\admin_panel\ClassroomController;

/*
|--------------------------------------------------------------------------
| API Routes Examples
|--------------------------------------------------------------------------
|
| Here are example routes showing how to use the custom middlewares
| with the controllers. These demonstrate the proper middleware usage.
|
*/

// Authentication routes (no middleware needed as they handle auth)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Admin Panel Routes - Require authentication + admin role
Route::prefix('admin')->group(function () {
    
    // Contest Management
    Route::controller(ContestController::class)->group(function () {
        Route::get('/contests', 'ListContests');
        Route::post('/contests', 'CreateContest');
        Route::get('/contests/{id}', 'GetContest');
        Route::put('/contests/{id}', 'UpdateContest');
        Route::delete('/contests/{id}', 'DeleteContest');
        Route::get('/contests/search', 'SearchContests');
        Route::get('/contests/{id}/stats', 'GetContestStats');
        Route::post('/contests/{id}/campuses', 'ManageCampusAssociation');
        Route::post('/contests/{id}/classrooms', 'ManageClassroomAssociation');
    });

    // Campus Management
    Route::controller(CampusController::class)->group(function () {
        Route::get('/campuses', 'ListCampuses');
        Route::post('/campuses', 'CreateCampus');
        Route::get('/campuses/{id}', 'GetCampus');
        Route::put('/campuses/{id}', 'UpdateCampus');
        Route::delete('/campuses/{id}', 'DeleteCampus');
        Route::get('/campuses/search', 'SearchCampuses');
    });

    // Classroom Management
    Route::controller(ClassroomController::class)->group(function () {
        Route::get('/classrooms', 'ListClassrooms');
        Route::post('/classrooms', 'CreateClassroom');
        Route::get('/classrooms/{id}', 'GetClassroom');
        Route::put('/classrooms/{id}', 'UpdateClassroom');
        Route::delete('/classrooms/{id}', 'DeleteClassroom');
        Route::delete('/classrooms/{id}/force', 'ForceDeleteClassroom');
        Route::get('/classrooms/search', 'SearchClassrooms');
        Route::get('/classrooms/campus/{campusId}', 'GetClassroomsByCampus');
        Route::get('/classrooms/{id}/stats', 'GetClassroomStats');
        Route::get('/classrooms/grouped', 'GetClassroomsGroupedByCampus');
        Route::post('/classrooms/bulk', 'BulkCreateClassrooms');
        Route::put('/classrooms/bulk', 'BulkUpdateClassrooms');
        Route::delete('/classrooms/bulk', 'BulkDeleteClassrooms');
        Route::post('/classrooms/transfer', 'TransferClassrooms');
        Route::get('/classrooms/available/{contestId?}', 'GetAvailableClassrooms');
    });
});

// Student Participation Routes - Require authentication + student role
Route::prefix('participations')->controller(ParticipationController::class)->group(function () {
    
    // Basic participation actions
    Route::get('/available-contests', 'GetAvailableContests');
    Route::post('/register', 'RegisterForContest');
    Route::get('/', 'GetMyParticipations');
    Route::get('/my-stats', 'GetMyStats');
    
    // Individual participation actions - require resource ownership
    Route::middleware(['resource.owner:participation'])->group(function () {
        Route::get('/{id}', 'GetParticipation');
        Route::delete('/{id}/cancel', 'CancelParticipation')
            ->middleware('contest.access', 'contest.time:before');
    });
    
    // Contest-specific actions with time restrictions
    Route::middleware(['contest.access'])->group(function () {
        Route::post('/{participationId}/submit-answers', 'SubmitAnswers')
            ->middleware('contest.time:during');
    });
    
    // Public contest information (after contest ends)
    Route::get('/contests/{contestId}/leaderboard', 'GetContestLeaderboard')
        ->middleware('contest.time:after');
});

// Role-based routes using the flexible RoleChecker middleware
Route::middleware(['auth:sanctum', 'role:admin,teacher'])->group(function () {
    // Routes accessible by both admin and teacher roles
    Route::get('/reports/contests', [ReportController::class, 'contestReports']);
    Route::get('/reports/students', [ReportController::class, 'studentReports']);
});

// Multi-role access example
Route::middleware(['auth:sanctum', 'role:admin,student,teacher'])->group(function () {
    // Routes accessible by multiple roles
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);
});

// Contest time window examples
Route::middleware(['auth:sanctum', 'student', 'contest.access'])->group(function () {
    
    // Actions allowed before contest starts
    Route::middleware('contest.time:before')->group(function () {
        Route::post('/contests/{contestId}/register', [ParticipationController::class, 'registerForContest']);
        Route::delete('/contests/{contestId}/cancel', [ParticipationController::class, 'cancelParticipation']);
    });
    
    // Actions allowed during contest
    Route::middleware('contest.time:during')->group(function () {
        Route::post('/contests/{contestId}/submit', [ParticipationController::class, 'submitAnswers']);
        Route::get('/contests/{contestId}/questions', [ParticipationController::class, 'getQuestions']);
    });
    
    // Actions allowed after contest ends
    Route::middleware('contest.time:after')->group(function () {
        Route::get('/contests/{contestId}/results', [ParticipationController::class, 'getResults']);
        Route::get('/contests/{contestId}/leaderboard', [ParticipationController::class, 'getLeaderboard']);
    });
    
    // Actions allowed before OR during contest
    Route::middleware('contest.time:before_or_during')->group(function () {
        Route::get('/contests/{contestId}/info', [ParticipationController::class, 'getContestInfo']);
    });
    
    // Actions NOT allowed during contest
    Route::middleware('contest.time:not_during')->group(function () {
        Route::put('/contests/{contestId}/preferences', [ParticipationController::class, 'updatePreferences']);
    });
});

// Complex middleware combinations example
Route::middleware(['auth:sanctum', 'student', 'contest.access', 'resource.owner:participation'])
    ->group(function () {
        Route::get('/my-participations/{id}/details', [ParticipationController::class, 'getDetailedParticipation']);
        Route::get('/my-participations/{id}/answers', [ParticipationController::class, 'getMyAnswers'])
            ->middleware('contest.time:after');
    });
