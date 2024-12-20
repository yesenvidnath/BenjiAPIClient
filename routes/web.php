<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\common\GroupChatController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', function () {
    return view('h');
});

Route::get('/payment/{encryptedDetails}', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');



Route::prefix('payment')->group(function () {
    Route::get('/return', [PaymentController::class, 'paymentReturn'])->name('payment.return');
    Route::get('/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
    Route::post('/notify', [PaymentController::class, 'paymentNotify'])->name('payment.notify');
});



Route::get('/reset-password/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');
