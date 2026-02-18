<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Clients\ClientController;
use App\Http\Controllers\Api\Onboarding\OrganizationController;
use App\Http\Controllers\Api\Onboarding\PlanController;
use App\Http\Controllers\Api\Subscriptions\SubscriptionController;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureOrganizationHasActiveSubscription;
use App\Http\Middleware\SetCurrentOrganization;
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

// Public routes
Route::post('/register', [RegisterController::class, 'register']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Onboarding routes
    Route::prefix('onboarding')->group(function () {
        Route::get('/plans', [PlanController::class, 'index']);
        Route::get('/plans/{plan}', [PlanController::class, 'show']);
        Route::post('/organizations', [OrganizationController::class, 'store']);
        Route::get('/organizations', [OrganizationController::class, 'index']);
    });

    // Organization routes
    Route::middleware([SetCurrentOrganization::class])->group(function () {
        // Subscription routes
        Route::prefix('organizations/{organization}/subscriptions')->group(function () {
            Route::middleware([EnsureOrganizationHasActiveSubscription::class])->group(function () {
                Route::get('/', [SubscriptionController::class, 'show']);
            });
            Route::post('/', [SubscriptionController::class, 'store']);
            Route::delete('/', [SubscriptionController::class, 'cancel']);
        });

        // Client Management Module
        Route::prefix('organizations/{organization}/clients')->group(function () {
            Route::middleware([
                EnsureOrganizationHasActiveSubscription::class,
                EnsureModuleAccess::class.':client-management',
            ])->group(function () {
                Route::get('/', [ClientController::class, 'index']);
                Route::post('/', [ClientController::class, 'store']);
                Route::get('/{client}', [ClientController::class, 'show']);
                Route::put('/{client}', [ClientController::class, 'update']);
                Route::delete('/{client}', [ClientController::class, 'destroy']);
            });
        });
    });
});
