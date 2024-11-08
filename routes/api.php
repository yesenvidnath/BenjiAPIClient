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

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->put('/profile', [UserController::class, 'updateProfile']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/income-sources/add', [IncomeManagementController::class, 'addIncomeSource']);
    Route::put('/income-sources/update/{id}', [IncomeManagementController::class, 'updateIncomeSource']);
    Route::delete('/income-sources/delete/{id}', [IncomeManagementController::class, 'deleteIncomeSource']);
    Route::get('/income-sources/search', [IncomeManagementController::class, 'searchIncomeSources']);
    Route::get('/income-sources/get/{id}', [IncomeManagementController::class, 'getIncomeSource']);
});
