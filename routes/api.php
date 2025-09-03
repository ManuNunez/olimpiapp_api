<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\admin_panel\CampusController;

// Rutas públicas (sin auth)
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

// Rutas protegidas por token
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); // en tu controlador el método se llama me()

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
