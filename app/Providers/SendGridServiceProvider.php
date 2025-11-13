<?php

namespace App\Providers;

use App\Mail\SendGridTransport;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class SendGridServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->resolving(MailManager::class, function (MailManager $mailManager) {
            $mailManager->extend('sendgrid_api', function ($config) {
                return new SendGridTransport(
                    $config['api_key'],
                    $config['from']['address'],
                    $config['from']['name'] ?? null
                );
            });
        });
    }
}