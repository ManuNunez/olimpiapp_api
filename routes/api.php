<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\admin_panel\CampusController;
use App\Http\Controllers\helpers\SchoolsHelperController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\ParticipationController;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::prefix('contests')->group(function () {
    Route::get('/{id}/status', [ContestController::class, 'getStatus']);
    Route::get('/', [ContestController::class, 'index']);
    Route::get('/{id}', [ContestController::class, 'show']);
    Route::post('/', [ContestController::class, 'store']);
    Route::put('/{id}', [ContestController::class, 'update']);
    Route::patch('/{id}', [ContestController::class, 'update']);
    Route::delete('/{id}', [ContestController::class, 'destroy']);
});

Route::controller(SchoolsHelperController::class)->group(function () {
    Route::get('/schools/closest', 'getClosestSchool');
    Route::get('/schools/by-cct', 'getSchoolByCct');
    Route::get('/schools/cct/{cct}', 'getSchoolByCctParam');
    Route::get('/schools/search-cct', 'searchSchoolsByCct');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('contests')->group(function () {
        Route::get('/', [ContestController::class, 'index']);
        Route::get('/{id}', [ContestController::class, 'show']);
        Route::post('/', [ContestController::class, 'store']);
        Route::put('/{id}', [ContestController::class, 'update']);
        Route::patch('/{id}', [ContestController::class, 'update']);
        Route::delete('/{id}', [ContestController::class, 'destroy']);
    });

    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);

    Route::controller(UserRoleController::class)->group(function () {
        Route::get('/users/{userId}/roles', 'getUserRoles');
        Route::post('/users/check-role', 'checkUserHasRole');
        Route::get('/users/{userId}/roles/{roleId}/check', 'checkUserHasRoleByParams');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index');
        Route::get('/users/{id}', 'show');
        Route::put('/users/{id}', 'update');
        Route::delete('/users/{id}', 'destroy');
        Route::patch('/users/{id}/restore', 'restore');
    });
    Route::post('/participations', [ParticipationController::class, 'store']);
    Route::get('/participations/contest/{contestId}', [ParticipationController::class, 'getByContest']);
    Route::get('/participations/contest/{contestId}/campus/{campusId}', [ParticipationController::class, 'getByContestAndCampus']);
    Route::get('/participations/student/{studentId}', [ParticipationController::class, 'getByStudent']);
    Route::get('/participations/check/{contestId}/student/{studentId}', [ParticipationController::class, 'isEnrolled']);
    Route::put('/participations/{id}', [ParticipationController::class, 'update']);


    Route::controller(StudentController::class)->group(function () {
        Route::get('/student/profile', 'getStudentByUser');
        Route::post('/student/profile', 'createStudent');
        Route::put('/student/profile', 'updateStudent');
        Route::patch('/student/profile', 'updateStudent');
        Route::match(['GET', 'POST', 'PUT', 'PATCH'], '/student/handle', 'handleStudent');
    });

    Route::middleware('student')->group(function () {
        Route::controller(StudentController::class)->group(function () {
            Route::get('/user/students', 'GetStudents');
        });
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('campus')->group(function () {
            Route::get('/', [CampusController::class, 'ListCampuses']);
            Route::get('/{id}', [CampusController::class, 'GetCampus']);
            Route::post('/create', [CampusController::class, 'CreateCampus']);
            Route::put('/update/{id}', [CampusController::class, 'UpdateCampus']);
            Route::delete('/{id}', [CampusController::class, 'DeleteCampus']);
            Route::post('/search', [CampusController::class, 'SearchCampuses']);
        });
    });
});
