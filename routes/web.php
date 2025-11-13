<?php

use App\Http\Controllers\AICreditsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChatIAController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialCategoryController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MarketingCampaignController;
use App\Http\Controllers\MedicalCertificateController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TelemedicineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Health check endpoint for production monitoring
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'env' => config('app.env'),
        'version' => config('app.version', '1.0.0')
    ]);
});

Route::get('/whatsapp', function () {
    Customer::where('id', 1)->first()->notify(new \App\Notifications\WhatsappNotification('mensagem de teste'));
})->name('testewhatsapp');

route::middleware(['auth', 'verified', 'company.active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    //group patients
    Route::group(['prefix' => 'patients', 'middleware' => 'can.access:patients'], function () {
        Route::get('/', [CustomerController::class, 'index'])->name('patients.index');
        // Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('patients.store');
        // Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        // Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::patch('/{id}', [CustomerController::class, 'update'])->name('patients.update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('patients.destroy');
    });

    //group users
    Route::group(['prefix' => 'users', 'middleware' => 'can.access:users'], function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::patch('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    //group appointments
    Route::group(['prefix' => 'appointments', 'middleware' => 'can.access:appointments'], function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::patch('/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

        // API routes for AJAX operations
        Route::patch('/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
        Route::post('/check-conflicts', [AppointmentController::class, 'checkConflicts'])->name('appointments.check-conflicts');
        Route::get('/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.available-slots');
    });

    //group services
    Route::group(['prefix' => 'services', 'middleware' => 'can.access:services'], function () {
        Route::get('/', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('/{id}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
    });

    //group rooms
    Route::group(['prefix' => 'rooms', 'middleware' => 'can.access:rooms'], function () {
        Route::get('/', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/', [RoomController::class, 'store'])->name('rooms.store');
        Route::patch('/{id}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    });

    //group financial
    Route::group(['prefix' => 'financial', 'middleware' => 'can.access:financial'], function () {
        // Dashboard financeiro
        Route::get('/', [FinancialController::class, 'index'])->name('financial.index');
        Route::get('/reports', [FinancialController::class, 'reports'])->name('financial.reports');

        // Transações
        Route::group(['prefix' => 'transactions'], function () {
            Route::get('/', [FinancialTransactionController::class, 'index'])->name('financial.transactions.index');
            Route::post('/', [FinancialTransactionController::class, 'store'])->name('financial.transactions.store');
            Route::patch('/{id}', [FinancialTransactionController::class, 'update'])->name('financial.transactions.update');
            Route::delete('/{id}', [FinancialTransactionController::class, 'destroy'])->name('financial.transactions.destroy');
            Route::patch('/{id}/mark-as-paid', [FinancialTransactionController::class, 'markAsPaid'])->name('financial.transactions.mark-as-paid');
            Route::get('/overdue', [FinancialTransactionController::class, 'overdue'])->name('financial.transactions.overdue');
            Route::get('/pending', [FinancialTransactionController::class, 'pending'])->name('financial.transactions.pending');
        });

        // Contas
        Route::group(['prefix' => 'accounts'], function () {
            Route::get('/', [FinancialAccountController::class, 'index'])->name('financial.accounts.index');
            Route::post('/', [FinancialAccountController::class, 'store'])->name('financial.accounts.store');
            Route::patch('/{id}', [FinancialAccountController::class, 'update'])->name('financial.accounts.update');
            Route::delete('/{id}', [FinancialAccountController::class, 'destroy'])->name('financial.accounts.destroy');
        });

        // Categorias
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [FinancialCategoryController::class, 'index'])->name('financial.categories.index');
            Route::post('/', [FinancialCategoryController::class, 'store'])->name('financial.categories.store');
            Route::patch('/{id}', [FinancialCategoryController::class, 'update'])->name('financial.categories.update');
            Route::delete('/{id}', [FinancialCategoryController::class, 'destroy'])->name('financial.categories.destroy');
        });
    });

    Route::group(['prefix' => 'medical-certificates', 'middleware' => 'can.access:medical-certificates'], function () {
        Route::get('/', [MedicalCertificateController::class, 'index'])->name('medical-certificates.index');
        Route::get('/create', [MedicalCertificateController::class, 'create'])->name('medical-certificates.create');
        Route::post('/', [MedicalCertificateController::class, 'store'])->name('medical-certificates.store');
        Route::get('/{medicalCertificate}', [MedicalCertificateController::class, 'show'])->name('medical-certificates.show');
        Route::get('/{medicalCertificate}/download', [MedicalCertificateController::class, 'download'])->name('medical-certificates.download');
        Route::delete('/{medicalCertificate}', [MedicalCertificateController::class, 'destroy'])->name('medical-certificates.destroy');
    });

    Route::group(['prefix' => 'billing', 'middleware' => 'can.access:billing'], function () {
        Route::get('/', [BillingController::class, 'billing'])->name('billing.index');
        Route::post('/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
        Route::post('/change-plan', [BillingController::class, 'changePlan'])->name('billing.change-plan');
    });

    Route::group(['prefix' => 'ai-credits', 'middleware' => 'can.access:ai-credits'], function () {
        Route::get('/', [AICreditsController::class, 'index'])->name('ai-credits.index');
        Route::get('/success', [AICreditsController::class, 'success'])->name('ai-credits.success');
        Route::post('/create-payment-intent', [AICreditsController::class, 'createPaymentIntent'])
            ->name('ai-credits.create-payment-intent');
        Route::post('/purchase-with-saved-card', [AICreditsController::class, 'purchaseWithSavedCard'])
            ->name('ai-credits.purchase-with-saved-card');
        Route::post('/consume', [AICreditsController::class, 'consumeCredits'])
            ->name('ai-credits.consume');
    });

    // Inventory Routes
    Route::group(['prefix' => 'inventory', 'middleware' => 'can.access:inventory'], function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');

        // Products
        Route::group(['prefix' => 'products'], function () {
            Route::get('/', [InventoryController::class, 'products'])->name('inventory.products.index');
            Route::post('/', [InventoryController::class, 'storeProduct'])->name('inventory.products.store');
            Route::patch('/{id}', [InventoryController::class, 'updateProduct'])->name('inventory.products.update');
            Route::delete('/{id}', [InventoryController::class, 'destroyProduct'])->name('inventory.products.destroy');
        });

        // Categories
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [InventoryController::class, 'categories'])->name('inventory.categories.index');
            Route::post('/', [InventoryController::class, 'storeCategory'])->name('inventory.categories.store');
            Route::patch('/{id}', [InventoryController::class, 'updateCategory'])->name('inventory.categories.update');
            Route::delete('/{id}', [InventoryController::class, 'destroyCategory'])->name('inventory.categories.destroy');
        });

        // Movements
        Route::group(['prefix' => 'movements'], function () {
            Route::get('/', [InventoryController::class, 'movements'])->name('inventory.movements.index');
            Route::post('/', [InventoryController::class, 'storeMovement'])->name('inventory.movements.store');
        });

        // Alerts
        Route::get('/alerts', [InventoryController::class, 'alerts'])->name('inventory.alerts.index');

        // Reports
        Route::get('/reports', [InventoryController::class, 'reports'])->name('inventory.reports.index');
    });

    // Marketing Routes
    Route::group(['prefix' => 'marketing', 'middleware' => 'can.access:marketing'], function () {
        Route::get('/campaigns', [MarketingCampaignController::class, 'index'])->name('marketing.campaigns.index');
        Route::post('/campaigns', [MarketingCampaignController::class, 'store'])->name('marketing.campaigns.store');
        Route::get('/campaigns/{id}', [MarketingCampaignController::class, 'show'])->name('marketing.campaigns.show');
        Route::patch('/campaigns/{id}', [MarketingCampaignController::class, 'update'])->name('marketing.campaigns.update');
        Route::delete('/campaigns/{id}', [MarketingCampaignController::class, 'destroy'])->name('marketing.campaigns.destroy');

        // Campaign actions
        Route::post('/campaigns/{id}/schedule', [MarketingCampaignController::class, 'schedule'])->name('marketing.campaigns.schedule');
        Route::post('/campaigns/{id}/cancel', [MarketingCampaignController::class, 'cancel'])->name('marketing.campaigns.cancel');
        Route::post('/campaigns/{id}/send-now', [MarketingCampaignController::class, 'sendNow'])->name('marketing.campaigns.send-now');
    });

    //group medical records
    Route::group(['prefix' => 'medical-records'], function () {
        Route::get('/', [MedicalRecordController::class, 'index'])->name('medicalRecords.index');
        Route::get('/patient/{customer}', [MedicalRecordController::class, 'show'])->name('medicalRecords.show');
        Route::get('/patient/{customer}/create', [MedicalRecordController::class, 'create'])->name('medicalRecords.create');
        Route::post('/', [MedicalRecordController::class, 'store'])->name('medicalRecords.store');
        Route::get('/{medicalRecord}/edit', [MedicalRecordController::class, 'edit'])->name('medicalRecords.edit');
        Route::patch('/{medicalRecord}', [MedicalRecordController::class, 'update'])->name('medicalRecords.update');
        Route::delete('/{medicalRecord}', [MedicalRecordController::class, 'destroy'])->name('medicalRecords.destroy');
        Route::get('/patient/{customer}/summary', [MedicalRecordController::class, 'summary'])->name('medicalRecords.summary');
    });

    Route::post('/chat', [ChatIAController::class, 'streamResponse'])->name('chat.stream');

    // Telemedicine - Sessões de telemedicina
    Route::group(['prefix' => 'telemedicine'], function () {
        Route::get('/config', [TelemedicineController::class, 'getConfig'])->name('telemedicine.config');
        Route::get('/credits', [TelemedicineController::class, 'getCredits'])->name('telemedicine.credits');
        Route::post('/sessions', [TelemedicineController::class, 'createSession'])->name('telemedicine.sessions.create');
        Route::get('/sessions/appointment/{appointmentId}', [TelemedicineController::class, 'getSessionByAppointment'])->name('telemedicine.sessions.by-appointment');
        Route::get('/sessions/active', [TelemedicineController::class, 'getActiveSessions'])->name('telemedicine.sessions.active');
        Route::patch('/sessions/{sessionId}', [TelemedicineController::class, 'updateSessionStatus'])->name('telemedicine.sessions.update-status');
        Route::post('/sessions/{sessionId}/end', [TelemedicineController::class, 'endSession'])->name('telemedicine.sessions.end');
        Route::post('/sessions/{sessionId}/notify', [TelemedicineController::class, 'notifyPatient'])->name('telemedicine.sessions.notify');
    });
});

//landing page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// verificar atestados
Route::get(
    '/medical-certificates/verify/{hash}',
    [MedicalCertificateController::class, 'verify']
)
    ->name('medical-certificates.verify');

// Webhook routes (no auth required)
// Sobrescrever a rota do Cashier para usar nosso WebhookController customizado
Route::post(
    'stripe/webhook',
    [WebhookController::class, 'handleWebhook']
)->name('cashier.webhook');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
