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
        // Acquire row-level lock to prevent concurrent processing
        $order = DB::transaction(function () use ($orderId) {
            $order = Order::query()
                ->whereKey($orderId)
                ->lockForUpdate()
                ->firstOrFail();

            // Skip if already processed by another worker
            if ($order->status !== Order::STATUS_PENDING) {
                return null;
            }

            return $order;
        });

        // Order was already processed
        if (!$order) {
            return;
        }

        try {
            // Call external payment gateway (outside transaction)
            $reference = $this->externalPayment->charge(
                amount: (float) $order->total,
                currency: 'USD',
                metadata: ['order_id' => $order->id]
            );

            // Persist successful payment result
            DB::transaction(function () use ($order, $reference) {
                $order->status = Order::STATUS_PAID;
                $order->external_reference = $reference;
                $order->save();

                OrderLog::create([
                    'order_id' => $order->id,
                    'event'    => 'status.paid',
                    'payload'  => [
                        'reference' => $reference,
                        'at'        => now()->toIso8601String(),
                    ],
                ]);
            });

        } catch (\Throwable $e) {
            // Persist failed payment result
            DB::transaction(function () use ($order, $e) {
                $order->status = Order::STATUS_FAILED;
                $order->save();

                OrderLog::create([
                    'order_id' => $order->id,
                    'event'    => 'payment.failed',
                    'payload'  => [
                        'error' => $e->getMessage(),
                        'at'    => now()->toIso8601String(),
                    ],
                ]);
            });

            Log::error('Payment processing failed', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);

            // Notify system about failure
            event(new OrderStatusChanged($order->fresh()));
            $this->telegram->notifyOperators($order);

            throw $e; // rethrow for queue retry mechanism
        }

        event(new OrderStatusChanged($order->fresh()));
        $this->telegram->notifyOperators($order);
    }
}
