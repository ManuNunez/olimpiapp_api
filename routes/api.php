<?php

use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(App\Http\Controllers\AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\AuthController::class, 'user']);
});
Route::middleware(['auth:sanctum', 'student'])->group(function () {
    Route::controller(App\Http\Controllers\StudentController::class)->group(function () {
        Route::get('/user/students', 'GetStudents');
    });
});
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(App\Http\Controllers\admin_panel\CampusController::class)->group(function () {
        Route::post('/campus/create', 'CreateCampus');
        Route::put('/campus/update/{id}', 'UpdateCampus');
        Route::delete('/campus/delete/{id}', 'DeleteCampus');
        Route::get('/campus/list', 'ListCampuses');
        Route::get('/campus/{id}', 'GetCampus');
        Route::get('/campus/search', 'SearchCampuses');
    });
});
