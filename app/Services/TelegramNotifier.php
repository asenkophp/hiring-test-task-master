<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function __construct(
        private readonly ?string $botToken,
        private readonly ?string $chatId,
    ) {
    }

    public function notifyOperators(Order $order): void
    {
        if (! $this->botToken || ! $this->chatId) {
            return;
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $this->botToken);

        $text = sprintf(
            "New order #%d\nCustomer: %s\nStatus: %s\nTotal: %s",
            $order->id,
            optional($order->customer)->name ?? 'guest',
            $order->status,
            $order->total
        );

        $response = Http::asForm()->post($url, [
            'chat_id'    => $this->chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);

        if (! $response->successful()) {
            Log::warning('Telegram notification failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
            ]);
        }
    }
}
