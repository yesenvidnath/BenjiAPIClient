<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\common\GroupChatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\customer\BookProfeshanal;

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

Route::get('/swagger', function () {
    return view('swagger');
});


// Admin
Route::get('/admin', function () {
    return view('admin/login');
});

Route::get('/admin/dashboard', function () {
    return view('admin/index');
});


// Profeshnal
// Route::get('/professional', function () {
//     return view('professional/login');
// });

// Route::get('/professional', function () {
//     return view('professional/login');
// });




Route::post('/finalize-meeting-payment', [BookProfeshanal::class, 'finalizeMeetingPayment'])->name('finalizeMeetingPayment');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/payment/{encryptedDetails}', [BookProfeshanal::class, 'showPaymentPage'])->name('payment.checkout');
    Route::post('/api/book-professional/finalize-payment', [BookProfeshanal::class, 'finalizeMeetingPayment']);
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
