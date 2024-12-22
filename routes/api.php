<?php

use App\Http\Controllers\common\GetAllProfessinoalsByCategory;
use App\Http\Controllers\Professionals\ProfileController;
//use App\Http\Controllers\common\GroupChatController;
use App\Http\Controllers\UserCommunicationController;
use App\Http\Controllers\IncomeManagementController;
use App\Http\Controllers\common\ExpensessController;
use App\Http\Controllers\admin\CategorieController;
use App\Http\Controllers\Customer\BookProfeshanal;
use App\Http\Controllers\Customer\BotResponse;
use App\Http\Controllers\admin\ReasonController;
use App\Http\Controllers\admin\BotController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\artisan\clean;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Route::middleware('auth:sanctum')->get('/usjer', function (Request $request) {
    return $request->user();
});

Route::get('/clear-cache', [Clean::class, 'ClearCache']);


// Service 01. User Management
Route::prefix('user-management-service')->group(function () {

    // Authentication-related routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            // Manage Profile routes
            Route::get('/profile/me', [UserController::class, 'getAuthenticatedUser']);
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

    // Notification management routes
    Route::prefix('notify')->middleware('auth:sanctum')->group(function () {
        Route::post('/send', [UserCommunicationController::class, 'sendNotification']);
        Route::post('/send-bulk', [UserCommunicationController::class, 'sendBulkNotification']);
        Route::post('/mark-read', [UserCommunicationController::class, 'markNotificationAsRead']);
        Route::delete('/delete', [UserCommunicationController::class, 'deleteNotification']);
    });

    // Route::prefix('expenses')->middleware('auth:sanctum')->group(function () {
    //     Route::post('/add-expense', [ExpensessController::class, 'addExpense']);
    // });

    // Profeshnal Management
    Route::middleware('auth:sanctum')->group(function (){

        Route::prefix('professional')->group(function () {
            Route::post('/convert-to-professional', [ProfileController::class, 'convertToProfessional']);
            Route::post('/update-profile', [ProfileController::class, 'updateProfessionalProfile']);
        });
    });
});


//Service 02. Expensess handling
Route::prefix('Expensess-Management-service')->group(function () {

    Route::get('/reasons/all', [ExpensessController::class, 'getAllReasons']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/add', [ExpensessController::class, 'addExpense']);
        // Change this to PUT and add the {expenseID} parameter
        Route::put('/update/{expenseID}', [ExpensessController::class, 'updateExpense']);
        // Delete an individual expense list item and its parent expense if no more items exist
        Route::delete('/delete-item/{expenseID}/{expenseListItemID}', [ExpensessController::class, 'deleteExpenseItem']);
        Route::get('/all', [ExpensessController::class, 'getAllExpenses']);

        // Income management routes
        Route::post('/income-sources/add', [IncomeManagementController::class, 'addIncomeSource']);
        Route::put('/income-sources/update/{id}', [IncomeManagementController::class, 'updateIncomeSource']);
        Route::delete('/income-sources/delete/{id}', [IncomeManagementController::class, 'deleteIncomeSource']);
        Route::get('/income-sources/search', [IncomeManagementController::class, 'searchIncomeSources']);
        Route::get('/income-sources/get/{id}', [IncomeManagementController::class, 'getIncomeSource']);
    });
});



// Service 03. AI Bot connection
Route::prefix('Bot-service')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/expenses', [BotController::class, 'getAllExpenses']);
    });
});

// Route::prefix('customer/bot')->middleware('auth:sanctum')->group(function () {
//     Route::get('/consolidated-data', [BotResponse::class, 'getConsolidatedUserData']);
// });


// Service 04. Meetings handling

Route::middleware('auth:sanctum')->prefix('customer')->group(function () {
    Route::post('/book-meeting', [BookProfeshanal::class, 'bookMeeting']);
    Route::get('/professionals/{type?}', [GetAllProfessinoalsByCategory::class, 'getAllProfessionalsByType']);
    Route::get('/consolidated-data', [BotResponse::class, 'getConsolidatedUserData']);
    Route::get('/current-month-data', [BotResponse::class, 'getCurrentMonthExpenses']);

});


Route::prefix('Meetings-Management-service')->group(function () {
    // Create a new meeting
    Route::post('/create', [MeetingController::class, 'createMeeting']);
    // Update an existing meeting
    Route::patch('/{meetingId}', [MeetingController::class, 'updateMeeting'])->name('api.meetings.update');
    // Delete a meeting
    Route::delete('/{meetingId}', [MeetingController::class, 'deleteMeeting'])->name('api.meetings.delete');
});


// Service 05. Payment handling

Route::prefix('Payment-Management-service')->middleware('auth:sanctum')->group(function () {
    Route::post('/create-payment', [PaymentController::class, 'createPayment']);
    Route::post('/payment-notify', [PaymentController::class, 'paymentNotify']);

    Route::post('/finalize-payment', [BookProfeshanal::class, 'finalizeMeetingPayment']);
});



// Service 06. Admin handling
Route::prefix('Admin-Management-service')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategorieController::class, 'index']);         // Get all categories
            Route::get('{id}', [CategorieController::class, 'show']);       // Get a single category by ID
            Route::post('/', [CategorieController::class, 'store']);
            Route::put('{id}', [CategorieController::class, 'update']);     // Update an existing category
            Route::delete('{id}', [CategorieController::class, 'destroy']); // Delete a category
        });

        Route::prefix('reasons')->group(function () {
            Route::get('/', [ReasonController::class, 'getAllReasons']);
            Route::post('/', [ReasonController::class, 'store']);        // Create a new reason
            Route::put('{id}', [ReasonController::class, 'update']);     // Update an existing reason
            Route::delete('{id}', [ReasonController::class, 'destroy']); // Delete a reason
        });
    });
});
