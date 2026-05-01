<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products  = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_CANCELLED,
        ];

        DB::transaction(function () use ($customers, $products, $statuses) {
            for ($i = 0; $i < 250; $i++) {
                $customer = $customers->random();
                $status   = $statuses[array_rand($statuses)];

                $order = Order::create([
                    'customer_id'        => $customer->id,
                    'status'             => $status,
                    'total'              => 0,
                    'external_reference' => $status === Order::STATUS_PAID
                        ? 'ref_' . bin2hex(random_bytes(6))
                        : null,
                    'metadata'           => ['source' => 'seeder'],
                ]);

                $itemsCount = random_int(8, 16);
                $total      = 0;

                $picked = $products->random(min($itemsCount, $products->count()));
                $picked = $picked instanceof Product ? collect([$picked]) : $picked;

                foreach ($picked as $product) {
                    $qty   = random_int(1, 3);
                    $price = (float) $product->price;
                    $total += $price * $qty;

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'unit_price' => $price,
                    ]);
                }

                $order->update(['total' => $total]);

                OrderLog::create([
                    'order_id' => $order->id,
                    'event'    => 'order.seeded',
                    'payload'  => ['initial_status' => $status],
                ]);
            }
        });
    }
}
