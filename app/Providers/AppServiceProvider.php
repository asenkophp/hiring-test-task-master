<?php

namespace App\Providers;

use App\Services\PaymentGateway;
use App\Services\TelegramNotifier;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function () {
            return new PaymentGateway(
                endpoint: config('services.payment.url'),
                apiKey: config('services.payment.key'),
            );
        });

        $this->app->singleton(TelegramNotifier::class, function () {
            return new TelegramNotifier(
                botToken: config('services.telegram.token'),
                chatId: config('services.telegram.chat_id'),
            );
        });
    }

    public function boot(): void
    {
    }
}
