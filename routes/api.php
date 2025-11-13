<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialCategoryController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MarketingCampaignController;
use App\Http\Controllers\MedicalCertificateController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\TelemedicineController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/webhook', function () {
    return response()->json(['message' => 'Webhook endpoint is active']);
})->name('webhook.active');

// Autenticação - Rotas públicas
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
});

// velidar atestado (medical-certificate)
Route::get('/medical-certificates/verify/{hash}', [MedicalCertificateController::class, 'verify'])->name('api.medical-certificates.verify');

// API Routes - Protegidas por autenticação e verificação de empresa ativa
Route::middleware(['auth:sanctum', 'company.active'])->group(function () {
    // Autenticação - Rotas protegidas
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
        Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
    });

    // Profile - Rotas de perfil do usuário
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('api.profile.show');
        Route::post('/', [ProfileController::class, 'update'])->name('api.profile.update');
        Route::post('/photo', [ProfileController::class, 'updatePhoto'])->name('api.profile.update-photo');
        Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('api.profile.delete-photo');
    });

    // Password - Atualização de senha
    Route::put('/password', [PasswordController::class, 'update'])->name('api.password.update');

    // Timezone - Horários de atendimento
    Route::get('/timezone', [ProfileController::class, 'timezone'])->name('api.timezone.show');
    Route::patch('/timezone', [ProfileController::class, 'updateTimezone'])->name('api.timezone.update');

    // Dashboard - Estatísticas e métricas gerais
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    // Customers (Patients)
    Route::apiResource('customers', CustomerController::class)->names('api.customers');

    // Appointments
    Route::get('/appointments/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('api.appointments.available-slots');
    Route::post('/appointments/check-conflicts', [AppointmentController::class, 'checkConflicts'])->name('api.appointments.check-conflicts');
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->name('api.appointments.update-status');
    Route::apiResource('appointments', AppointmentController::class)->names('api.appointments');

    // Rooms
    Route::apiResource('rooms', RoomController::class)->names('api.rooms');

    // Services
    Route::apiResource('services', ServiceController::class)->names('api.services');

    // Users
    Route::get('/users/{id}/working-hours', [UserController::class, 'getWorkingHours'])->name('api.users.working-hours');
    Route::apiResource('users', UserController::class)->names('api.users');

    // Medical Records
    Route::get('/medical-records/customer/{customerId}', [MedicalRecordController::class, 'getByCustomer'])->name('api.medical-records.by-customer');
    Route::get('/medical-records/{id}', [MedicalRecordController::class, 'showById'])->name('api.medical-records.show');
    Route::apiResource('medical-records', MedicalRecordController::class)->except(['show'])->names('api.medical-records');

    // Medical Certificates
    Route::apiResource('medical-certificates', MedicalCertificateController::class)->names('api.medical-certificates');

    // Marketing Campaigns
    Route::apiResource('marketing-campaigns', MarketingCampaignController::class)->names('api.marketing-campaigns');

    // Financial - Accounts
    Route::apiResource('financial-accounts', FinancialAccountController::class)->names('api.financial-accounts');

    // Financial - Categories
    Route::apiResource('financial-categories', FinancialCategoryController::class)->names('api.financial-categories');

    // Financial - Transactions
    Route::apiResource('financial-transactions', FinancialTransactionController::class)->names('api.financial-transactions');

    // Inventory - Products
    Route::prefix('inventory')->group(function () {
        Route::get('products', [InventoryController::class, 'products'])->name('api.inventory.products.index');
        Route::post('products', [InventoryController::class, 'storeProduct'])->name('api.inventory.products.store');
        Route::put('products/{id}', [InventoryController::class, 'updateProduct'])->name('api.inventory.products.update');
        Route::delete('products/{id}', [InventoryController::class, 'destroyProduct'])->name('api.inventory.products.destroy');
    });

    // Telemedicine - Sessões de telemedicina
    Route::prefix('telemedicine')->group(function () {
        Route::get('/config', [TelemedicineController::class, 'getConfig'])->name('api.telemedicine.config');
        Route::get('/credits', [TelemedicineController::class, 'getCredits'])->name('api.telemedicine.credits');
        Route::post('/sessions', [TelemedicineController::class, 'createSession'])->name('api.telemedicine.sessions.create');
        Route::get('/sessions/appointment/{appointmentId}', [TelemedicineController::class, 'getSessionByAppointment'])->name('api.telemedicine.sessions.by-appointment');
        Route::get('/sessions/active', [TelemedicineController::class, 'getActiveSessions'])->name('api.telemedicine.sessions.active');
        Route::patch('/sessions/{sessionId}', [TelemedicineController::class, 'updateSessionStatus'])->name('api.telemedicine.sessions.update-status');
        Route::post('/sessions/{sessionId}/end', [TelemedicineController::class, 'endSession'])->name('api.telemedicine.sessions.end');
        Route::post('/sessions/{sessionId}/notify', [TelemedicineController::class, 'notifyPatient'])->name('api.telemedicine.sessions.notify');
    });
});
