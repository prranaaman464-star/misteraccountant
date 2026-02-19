<?php

use App\Http\Controllers\Auth\CheckEmailController;
use App\Http\Controllers\Auth\EmailController;
use App\Http\Controllers\CurrentOrganizationController;
use App\Http\Controllers\GetStarted\PlanController as GetStartedPlanController;
use App\Http\Controllers\Inventory\ItemsController;
use App\Http\Controllers\Inventory\WarehousesController;
use App\Http\Controllers\Inventory\ProductWisePlController;
use App\Http\Controllers\Manage\ManageController;
use App\Http\Controllers\Onboarding\OrganizationController;
use App\Http\Controllers\Onboarding\PlanController;
use App\Http\Controllers\PlanDetailController;
use App\Http\Controllers\PostAuthRedirectController;
use App\Http\Controllers\RenewSubscriptionController;
use App\Http\Middleware\RedirectIfNoOrganization;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Inventory\StockValueReportController;
use App\Http\Controllers\Inventory\BatchExpiryReportController;
use App\Http\Controllers\Inventory\PartyTransactionsController;
use App\Http\Controllers\Inventory\AllTransactionsController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// SaasyKit-style: Plan first (public), then email, then login or signup
Route::get('plans', [GetStartedPlanController::class, 'index'])->name('plans.index');
Route::post('plans/select', [GetStartedPlanController::class, 'select'])->name('plans.select');

Route::middleware('guest')->group(function () {
    Route::get('auth/email', [EmailController::class, 'show'])->name('auth.email');
    Route::post('check-email', [CheckEmailController::class, 'check'])->name('check-email');
});

Route::get('after-auth', PostAuthRedirectController::class)->middleware(['auth', 'verified'])->name('after-auth');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware([RedirectIfNoOrganization::class])->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
        Route::get('plan', PlanDetailController::class)->name('plan.show');
        Route::post('plan/renew', RenewSubscriptionController::class)->name('plan.renew');
        Route::put('current-organization', [CurrentOrganizationController::class, 'update'])->name('current-organization.update');

        Route::prefix('manage')->name('manage.')->group(function () {
            Route::get('/', [ManageController::class, 'index'])->name('index');
            Route::get('users', [ManageController::class, 'users'])->name('users');
            Route::post('members', [ManageController::class, 'storeMember'])->name('members.store');
            Route::get('memberships', [ManageController::class, 'memberships'])->name('memberships');
            Route::get('permissions', [ManageController::class, 'permissions'])->name('permissions');
            Route::post('permissions', [ManageController::class, 'storePermission'])->name('permissions.store');
        });
    });

    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    });
});

Route::middleware(['auth', 'verified', RedirectIfNoOrganization::class])->prefix('inventory')->group(function () {
    Route::get('items', [ItemsController::class, 'index'])->name('inventory.items');
    Route::get('items/create', [ItemsController::class, 'create'])->name('inventory.items.create');
    Route::post('items', [ItemsController::class, 'store'])->name('inventory.items.store');
    Route::post('categories', [ItemsController::class, 'storeCategory'])->name('inventory.categories.store');
    Route::get('items/{item}', [ItemsController::class, 'show'])->name('inventory.items.show');
    Route::get('items/{item}/edit', [ItemsController::class, 'edit'])->name('inventory.items.edit');
    Route::put('items/{item}', [ItemsController::class, 'update'])->name('inventory.items.update');
    Route::delete('items/{item}', [ItemsController::class, 'destroy'])->name('inventory.items.destroy');

    //Warehouses
    Route::get('warehouses', [WarehousesController::class, 'index'])->name('inventory.warehouses');

    //product-wise-pl
    Route::get('product-wise-pl', [ProductWisePlController::class, 'index'])->name('inventory.product-wise-pl');

    //stock-value-report
    Route::get('stock-value-report', [StockValueReportController::class, 'index'])->name('inventory.stock-value-report');

    //batch-expiry-report
    Route::get('batch-expiry-report', [BatchExpiryReportController::class, 'index'])->name('inventory.batch-expiry-report');

    //party-transactions
    Route::get('party-transactions', [PartyTransactionsController::class, 'index'])->name('inventory.party-transactions');

    //all-transactions
    Route::get('all-transactions', [AllTransactionsController::class, 'index'])->name('inventory.all-transactions');

});

require __DIR__.'/settings.php';
