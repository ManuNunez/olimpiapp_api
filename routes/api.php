<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\admin_panel\CampusController;
use App\Http\Controllers\helpers\SchoolsHelperController;
use App\Http\Controllers\UserRoleController; // Agregar esta línea

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
    
    // Estudiantes (con middleware extra "student")
    Route::middleware('student')->group(function () {
        Route::controller(StudentController::class)->group(function () {
            Route::get('/user/students', 'GetStudents');
        });
    });
    
    // Campus (admin panel)
    Route::controller(CampusController::class)->group(function () {
        Route::post('/campus/create', 'CreateCampus');
        Route::put('/campus/update/{id}', 'UpdateCampus');
        Route::delete('/campus/delete/{id}', 'DeleteCampus');
        Route::get('/campus/list', 'ListCampuses');
        Route::get('/campus/{id}', 'GetCampus');
        Route::get('/campus/search', 'SearchCampuses');
    });
});