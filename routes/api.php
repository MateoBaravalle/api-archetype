<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth', [AuthController::class, 'store'])->name('auth.store');
    Route::get('/auth', [AuthController::class, 'show'])->middleware('auth:sanctum')->name('auth.show');
    Route::delete('/auth', [AuthController::class, 'destroy'])->middleware('auth:sanctum')->name('auth.destroy');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Routes for tasks
        Route::apiResource('tasks', TaskController::class);

        // User profile routes
        Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile');
        Route::put('/users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
        Route::put('/users/password', [UserController::class, 'updatePassword'])->name('users.password.update');
    });
});
