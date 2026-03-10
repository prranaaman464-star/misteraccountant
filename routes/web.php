<?php

use App\Http\Controllers\Auth\CheckEmailController;
use App\Http\Controllers\Auth\EmailController;
use App\Http\Controllers\CurrentOrganizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GetStarted\PlanController as GetStartedPlanController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\Inventory\AllTransactionsController;
use App\Http\Controllers\Inventory\BatchExpiryReportController;
use App\Http\Controllers\Inventory\ItemsController;
use App\Http\Controllers\Inventory\PartyTransactionsController;
use App\Http\Controllers\Inventory\ProductWisePlController;
use App\Http\Controllers\Inventory\StockValueReportController;
use App\Http\Controllers\Inventory\WarehousesController;
use App\Http\Controllers\Purchase\DebitNotesController;
use App\Http\Controllers\Purchase\HireTheBestVendorsController;
use App\Http\Controllers\Purchase\PayoutReceiptsController;
use App\Http\Controllers\Purchase\PurchaseOrdersController;
use App\Http\Controllers\Purchase\PurchasesAndExpensesController;
use App\Http\Controllers\Purchase\VendorsAndSuppliersController;
use App\Http\Controllers\Purchase\VendorsLeadsController;
use App\Http\Controllers\Sale\ClientsAndProspectsController;
use App\Http\Controllers\Sale\CreditNotesController;
use App\Http\Controllers\Sale\DeliveryChallansController;
use App\Http\Controllers\Sale\InvoicesController;
use App\Http\Controllers\Sale\PaymentReceiptsController;
use App\Http\Controllers\Sale\ProformaInvoicesController;
use App\Http\Controllers\Sale\QuotationAndEstimatesController;
use App\Http\Controllers\Sale\SalesOrdersController;
use App\Http\Controllers\Manage\ManageController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Onboarding\OrganizationController as OnboardingOrganizationController;
use App\Http\Controllers\Onboarding\PlanController;
use App\Http\Controllers\PlanDetailController;
use App\Http\Controllers\PostAuthRedirectController;
use App\Http\Controllers\RenewSubscriptionController;
use App\Http\Controllers\Superadmin\CompaniesController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\DomainController;
use App\Http\Controllers\Superadmin\PackagesController;
use App\Http\Controllers\Superadmin\PurchaseTransactionController;
use App\Http\Controllers\Superadmin\SubscriptionsController;
use App\Http\Controllers\Superadmin\SwitchOrganizationController;
use App\Http\Middleware\EnsureSuperadmin;
use App\Http\Middleware\RedirectIfNoOrganization;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

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
    Route::middleware([EnsureSuperadmin::class])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('dashboard', SuperadminDashboardController::class)->name('dashboard');
        Route::get('companies', [CompaniesController::class, 'index'])->name('companies.index');
        Route::post('switch-organization', SwitchOrganizationController::class)->name('switch-organization');
        Route::get('subscriptions', [SubscriptionsController::class, 'index'])->name('subscriptions.index');
        Route::get('packages', [PackagesController::class, 'index'])->name('packages.index');
        Route::get('domain', [DomainController::class, 'index'])->name('domain.index');
        Route::get('purchase-transaction', [PurchaseTransactionController::class, 'index'])->name('purchase-transaction.index');
    });

    Route::middleware([RedirectIfNoOrganization::class])->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('plan', PlanDetailController::class)->name('plan.show');
        Route::post('plan/renew', RenewSubscriptionController::class)->name('plan.renew');
        Route::put('current-organization', [CurrentOrganizationController::class, 'update'])->name('current-organization.update');
        Route::get('organization', [OrganizationController::class, 'show'])->name('organization.show');
        Route::put('organization/{organization}', [OrganizationController::class, 'update'])->name('organization.update');

        Route::prefix('manage')->name('manage.')->group(function () {
            Route::get('/', [ManageController::class, 'index'])->name('index');
            Route::get('users', [ManageController::class, 'users'])->name('users');
            Route::get('members/csv', [ManageController::class, 'membersCsv'])->name('members.csv');
            Route::post('members/bulk-remove', [ManageController::class, 'bulkRemoveMembers'])->name('members.bulk-remove');
            Route::post('members', [ManageController::class, 'storeMember'])->name('members.store');
            Route::put('members/{user}', [ManageController::class, 'updateMember'])->name('members.update');
            Route::delete('members/{user}', [ManageController::class, 'destroyMember'])->name('members.destroy');
            Route::get('memberships', [ManageController::class, 'memberships'])->name('memberships');
            Route::get('permissions', [ManageController::class, 'permissions'])->name('permissions');
            Route::post('permissions', [ManageController::class, 'storePermission'])->name('permissions.store');
        });
    });

    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('organizations/create', [OnboardingOrganizationController::class, 'create'])->name('organizations.create');
        Route::post('organizations', [OnboardingOrganizationController::class, 'store'])->name('organizations.store');
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    });
});

Route::get('search', GlobalSearchController::class)
    ->middleware(['auth', 'verified', RedirectIfNoOrganization::class])
    ->name('search');

Route::middleware(['auth', 'verified', RedirectIfNoOrganization::class])->prefix('inventory')->group(function () {
    Route::get('items', [ItemsController::class, 'index'])->name('inventory.items');
    Route::get('items/create', [ItemsController::class, 'create'])->name('inventory.items.create');
    Route::post('items', [ItemsController::class, 'store'])->name('inventory.items.store');
    Route::post('categories', [ItemsController::class, 'storeCategory'])->name('inventory.categories.store');
    Route::get('items/{item}', [ItemsController::class, 'show'])->name('inventory.items.show');
    Route::get('items/{item}/edit', [ItemsController::class, 'edit'])->name('inventory.items.edit');
    Route::put('items/{item}', [ItemsController::class, 'update'])->name('inventory.items.update');
    Route::get('items/csv', [ItemsController::class, 'csv'])->name('inventory.items.csv');
    Route::delete('items/{item}', [ItemsController::class, 'destroy'])->name('inventory.items.destroy');
    Route::post('items/bulk-destroy', [ItemsController::class, 'bulkDestroy'])->name('inventory.items.bulk-destroy');
    Route::post('items/{item}/stock-in', [ItemsController::class, 'stockIn'])->name('inventory.items.stock-in');
    Route::post('items/{item}/stock-out', [ItemsController::class, 'stockOut'])->name('inventory.items.stock-out');

    // Warehouses
    Route::get('warehouses', [WarehousesController::class, 'index'])->name('inventory.warehouses');

    // product-wise-pl
    Route::get('product-wise-pl', [ProductWisePlController::class, 'index'])->name('inventory.product-wise-pl');
    Route::get('product-wise-pl/csv', [ProductWisePlController::class, 'csv'])->name('inventory.product-wise-pl.csv');

    // stock-value-report
    Route::get('stock-value-report', [StockValueReportController::class, 'index'])->name('inventory.stock-value-report');

    // batch-expiry-report
    Route::get('batch-expiry-report', [BatchExpiryReportController::class, 'index'])->name('inventory.batch-expiry-report');

    // party-transactions
    Route::get('party-transactions', [PartyTransactionsController::class, 'index'])->name('inventory.party-transactions');

    // all-transactions
    Route::get('all-transactions', [AllTransactionsController::class, 'index'])->name('inventory.all-transactions');
});

Route::middleware(['auth', 'verified', RedirectIfNoOrganization::class])->prefix('purchases')->name('purchases.')->group(function () {
    Route::get('vendors-leads', [VendorsLeadsController::class, 'index'])->name('vendors-leads');
    Route::get('vendors-and-suppliers', [VendorsAndSuppliersController::class, 'index'])->name('vendors-and-suppliers');
    Route::get('purchases-and-expenses', [PurchasesAndExpensesController::class, 'index'])->name('purchases-and-expenses');
    Route::get('purchase-orders', [PurchaseOrdersController::class, 'index'])->name('purchase-orders');
    Route::get('payout-receipts', [PayoutReceiptsController::class, 'index'])->name('payout-receipts');
    Route::get('debit-notes', [DebitNotesController::class, 'index'])->name('debit-notes');
    Route::get('hire-the-best-vendors', [HireTheBestVendorsController::class, 'index'])->name('hire-the-best-vendors');
});

Route::middleware(['auth', 'verified', RedirectIfNoOrganization::class])->prefix('sales')->name('sales.')->group(function () {
    Route::get('clients-and-prospects', [ClientsAndProspectsController::class, 'index'])->name('clients-and-prospects');
    Route::get('clients-and-prospects/create', [ClientsAndProspectsController::class, 'create'])->name('clients-and-prospects.create');
    Route::post('clients-and-prospects', [ClientsAndProspectsController::class, 'store'])->name('clients-and-prospects.store');
    Route::get('quotation-and-estimates', [QuotationAndEstimatesController::class, 'index'])->name('quotation-and-estimates');
    Route::get('proforma-invoices', [ProformaInvoicesController::class, 'index'])->name('proforma-invoices');
    Route::get('invoices', [InvoicesController::class, 'index'])->name('invoices');
    Route::get('invoices/create', [InvoicesController::class, 'create'])->name('invoices.create');
    Route::get('invoices/templates', [InvoicesController::class, 'templates'])->name('invoices.templates');
    Route::get('invoices/recurring', [InvoicesController::class, 'recurring'])->name('invoices.recurring');
    Route::get('payment-receipts', [PaymentReceiptsController::class, 'index'])->name('payment-receipts');
    Route::get('sales-orders', [SalesOrdersController::class, 'index'])->name('sales-orders');
    Route::get('delivery-challans', [DeliveryChallansController::class, 'index'])->name('delivery-challans');
    Route::get('credit-notes', [CreditNotesController::class, 'index'])->name('credit-notes');
});

require __DIR__.'/settings.php';
