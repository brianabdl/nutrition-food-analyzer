<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Api;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
    Route::get('/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'resetPassword'])->name('password.reset');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/',           [FoodController::class,      'index'])->name('foods.index');
    Route::get('/comparison', [ComparisonController::class,'index'])->name('comparison');
    Route::get('/about',      [AboutController::class,     'index'])->name('about');
    Route::get('/profile',    [ProfileController::class,   'index'])->name('profile');

    Route::get('/foods/{name}/nutrition', [NutritionController::class, 'show'])
         ->name('nutrition.show')
         ->where('name', '.*');

    // API routes (session-auth, JSON)
    Route::prefix('api')->group(function () {
        Route::get('/foods', [\App\Http\Controllers\Api\FoodController::class, 'search'])->name('api.foods.search');
    });
});
