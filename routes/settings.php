<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Models\WhatsAppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'company.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/timezone', [ProfileController::class, 'timezone'])->name('profile.timezone');
    Route::patch('settings/timezone', [ProfileController::class, 'updateTimezone'])->name('profile.updateTimezone');

    Route::get('settings/whatsapp', [ProfileController::class, 'whatsapp'])->name('whatsapp.config')->middleware('can.access:settings');
    Route::patch('settings/whatsapp/messages', [ProfileController::class, 'updateWhatsappMessages'])->name('whatsapp.updateMessages')->middleware('can.access:settings');
    Route::get('settings/whatsapp/qrcode', function () {
        $config = WhatsAppConfig::first();

        $response = Http::withHeaders([
            'apiKey' => env('EVOLUTION_API_KEY'),
        ])->get(env('WHATSAPP_API_URL') . '/instance/connect/' . $config->instance_name);

        return $response->json();
    });

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');
});
