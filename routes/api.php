<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\admin_panel\CampusController;
use App\Http\Controllers\helpers\SchoolsHelperController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserController; // Importar UserController

// Rutas públicas (sin auth)
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

// Rutas helpers públicas actualizadas
Route::controller(SchoolsHelperController::class)->group(function () {
    // Ruta existente - buscar 6 escuelas más cercanas
    Route::get('/schools/closest', 'getClosestSchool');
    // Nueva ruta - obtener escuela por CCT (usando query parameter)
    Route::get('/schools/by-cct', 'getSchoolByCct');
    // Nueva ruta - obtener escuela por CCT (usando parámetro de ruta)
    Route::get('/schools/cct/{cct}', 'getSchoolByCctParam');
    // Nueva ruta - buscar escuelas por CCT parcial
    Route::get('/schools/search-cct', 'searchSchoolsByCct');
});

// Rutas protegidas por token
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); // en tu controlador el método se llama me()
    
    // User Roles - Rutas protegidas
    Route::controller(UserRoleController::class)->group(function () {
        Route::get('/users/{userId}/roles', 'getUserRoles');
        Route::post('/users/check-role', 'checkUserHasRole');
        Route::get('/users/{userId}/roles/{roleId}/check', 'checkUserHasRoleByParams');
    });

    // NUEVAS RUTAS PARA USER MANAGEMENT
    Route::controller(UserController::class)->group(function () {
        Route::put('/users/{id}', 'update');     // Actualizar usuario
        Route::delete('/users/{id}', 'destroy'); // Desactivar usuario
        Route::patch('/users/{id}/restore', 'restore'); // Reactivar usuario
    });

    // NUEVAS RUTAS PARA STUDENT MANAGEMENT
    Route::controller(StudentController::class)->group(function () {
        // Obtener estudiante del usuario autenticado
        Route::get('/student/profile', 'getStudentByUser');
        
        // Crear nuevo perfil de estudiante
        Route::post('/student/profile', 'createStudent');
        
        // Actualizar perfil de estudiante existente
        Route::put('/student/profile', 'updateStudent');
        Route::patch('/student/profile', 'updateStudent');
        
        // Ruta combinada que maneja toda la lógica (opcional)
        Route::match(['GET', 'POST', 'PUT', 'PATCH'], '/student/handle', 'handleStudent');
    });

    // Estudiantes (con middleware extra "student") - RUTAS EXISTENTES
    Route::middleware('student')->group(function () {
        Route::controller(StudentController::class)->group(function () {
            Route::get('/user/students', 'GetStudents');
        });
    });

    // Campus (admin panel)

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('campus')->group(function () {
        Route::get('/', [CampusController::class, 'ListCampuses']);   // GET api/campuses
        Route::get('/{id}', [CampusController::class, 'GetCampus']);  // GET api/campuses/1
        Route::post('/create', [CampusController::class, 'CreateCampus']);  // POST api/campuses
        Route::put('/update/{id}', [CampusController::class, 'UpdateCampus']); // PUT api/campuses/1
        Route::delete('/{id}', [CampusController::class, 'DeleteCampus']); // DELETE api/campuses/1
        Route::post('/search', [CampusController::class, 'SearchCampuses']); // POST api/campuses/search
    });
});

});
