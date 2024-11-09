<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IncomeManagementController;

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

Route::prefix('user-management-service')->group(function () {

    // Authentication-related routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            // Manage Profile routes
            Route::put('/profile/update', [UserController::class, 'updateProfile']);
            Route::delete('/profile/delete', [UserController::class, 'deleteProfile']);
            Route::get('/profile/get/{id}', [UserController::class, 'getProfile']);
            Route::get('/profile/search', [UserController::class, 'searchProfiles']);
            Route::post('/change-password', [UserController::class, 'changePassword']);

            // Certification routes for professionals under auth/profile
            Route::prefix('profile')->group(function () {
                Route::post('/add-certification', [UserController::class, 'addCertification']);
                Route::put('/update-certification/{id}', [UserController::class, 'updateCertification']);
                Route::delete('/delete-certification/{id}', [UserController::class, 'deleteCertification']);
                Route::get('/get-certification/{id}', [UserController::class, 'getCertification']);
                Route::get('/search-certification', [UserController::class, 'searchCertification']);
            });
        });
    });

    // Income management routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/income-sources/add', [IncomeManagementController::class, 'addIncomeSource']);
        Route::put('/income-sources/update/{id}', [IncomeManagementController::class, 'updateIncomeSource']);
        Route::delete('/income-sources/delete/{id}', [IncomeManagementController::class, 'deleteIncomeSource']);
        Route::get('/income-sources/search', [IncomeManagementController::class, 'searchIncomeSources']);
        Route::get('/income-sources/get/{id}', [IncomeManagementController::class, 'getIncomeSource']);
    });
});

