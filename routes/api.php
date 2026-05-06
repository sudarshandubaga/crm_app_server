<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FirmController;
use App\Http\Controllers\MessageTemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Auth (public) ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'publicRegister']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
});

// ── Location (Public) ───────────────────────────────────────────────────────
Route::prefix('locations')->group(function () {
    Route::get('countries', [LocationController::class, 'getCountries']);
    Route::get('states', [LocationController::class, 'getStates']);
    Route::get('cities', [LocationController::class, 'getCities']);
});

// ── Authenticated Routes ───────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Reports
    Route::get('reports/summary', [ReportController::class, 'summary']);

    // Locations (Authenticated Add)
    Route::prefix('locations')->group(function () {
        Route::post('countries', [LocationController::class, 'addCountry']);
        Route::post('states', [LocationController::class, 'addState']);
        Route::post('cities', [LocationController::class, 'addCity']);
    });

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('register', [AuthController::class, 'register']); // admin creates staff
    });

    // Firm
    Route::prefix('firm')->group(function () {
        Route::get('/', [FirmController::class, 'show']);
        Route::put('/', [FirmController::class, 'update']);
    });

    // Users (firm-scoped)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::delete('{user}', [UserController::class, 'destroy']);
    });

    // Contacts
    Route::apiResource('contacts', ContactController::class);

    // Pipelines + Stages (nested)
    Route::apiResource('pipelines', PipelineController::class);
    Route::prefix('pipelines/{pipeline}/stages')->group(function () {
        Route::post('/', [PipelineController::class, 'storeStage']);
        Route::put('{stage}', [PipelineController::class, 'updateStage']);
        Route::delete('{stage}', [PipelineController::class, 'destroyStage']);
    });

    // Leads
    Route::apiResource('leads', LeadController::class);

    // Activities — firm-wide list (for Follow-ups tab)
    Route::get('activities', [ActivityController::class, 'firmIndex']);

    // Activities (nested under lead)
    Route::prefix('leads/{lead}/activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::post('/', [ActivityController::class, 'store']);
        Route::put('{activity}', [ActivityController::class, 'update']);
        Route::delete('{activity}', [ActivityController::class, 'destroy']);
    });

    // Notes (nested under lead)
    Route::prefix('leads/{lead}/notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::put('{note}', [NoteController::class, 'update']);
        Route::delete('{note}', [NoteController::class, 'destroy']);
    });

    // Activity Logs
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);                      // firm-wide (admin)
        Route::get('leads/{lead}', [ActivityLogController::class, 'forLead']);         // per lead
    });

    // Custom Fields
    Route::prefix('custom-fields')->group(function () {
        // Categories
        Route::get('categories', [CustomFieldController::class, 'categories']);
        Route::post('categories', [CustomFieldController::class, 'storeCategory']);
        Route::put('categories/{customFieldCategory}', [CustomFieldController::class, 'updateCategory']);
        Route::delete('categories/{customFieldCategory}', [CustomFieldController::class, 'destroyCategory']);

        // Fields (under a category)
        Route::post('categories/{customFieldCategory}/fields', [CustomFieldController::class, 'storeField']);
        Route::put('fields/{customField}', [CustomFieldController::class, 'updateField']);
        Route::delete('fields/{customField}', [CustomFieldController::class, 'destroyField']);

        // Save values for lead/contact
        Route::post('values', [CustomFieldController::class, 'saveValues']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::put('{notification}/read', [NotificationController::class, 'markRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
    });

    // Message Templates
    Route::apiResource('message-templates', MessageTemplateController::class);

    // Subscriptions & Payments
    Route::get('plans', [\App\Http\Controllers\PlanController::class, 'index']);
    Route::post('payments/create-order', [\App\Http\Controllers\PaymentController::class, 'createOrder']);
    Route::post('payments/verify', [\App\Http\Controllers\PaymentController::class, 'verifyPayment']);
});
