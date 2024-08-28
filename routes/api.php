<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/login-social', 'socialLogin');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
});

Route::controller(UserController::class)->middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', 'showProfile');
    Route::post('/profile/update', 'updateProfile');
});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
