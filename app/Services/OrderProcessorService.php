<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProcessorService
{
    public function __construct(
        private readonly PaymentGateway $externalPayment,
        private readonly TelegramNotifier $telegram,
    ) {
    }

    /**
     * Process a paid order coming from the queue.
     * Updates the local DB status and notifies the external payment gateway.
     */
    public function handle(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        DB::transaction(function () use ($order) {
            $order->status = Order::STATUS_PAID;
            $order->save();

            OrderLog::create([
                'order_id' => $order->id,
                'event'    => 'status.paid',
                'payload'  => ['actor' => 'queue', 'at' => now()->toIso8601String()],
            ]);
        });

        try {
            $reference = $this->externalPayment->charge(
                amount: (float) $order->total,
                currency: 'USD',
                metadata: ['order_id' => $order->id]
            );

            $order->external_reference = $reference;
            $order->save();
        } catch (\Throwable $e) {
            Log::error('External payment gateway charge failed', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
        }

        event(new OrderStatusChanged($order->fresh()));

        $this->telegram->notifyOperators($order);
    }
}
