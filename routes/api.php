<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']); // Login to get token

Route::middleware('auth:sanctum')->group(function () {
    Route::post('tasks', [TaskController::class, 'store']); // Create task
    Route::get('tasks', [TaskController::class, 'index']);       // List tasks
    Route::get('tasks/{id}', [TaskController::class, 'show']);   // Show task
    Route::put('tasks/{id}', [TaskController::class, 'update']); // Update task
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']); // Delete task
});
