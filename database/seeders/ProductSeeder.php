<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            'Mechanical Keyboard', 'Wireless Mouse', '27" 4K Monitor', 'USB-C Hub',
            'Noise-Cancelling Headphones', 'Webcam 1080p', 'Standing Desk Mat',
            'Laptop Stand', 'Ergonomic Chair', 'Desk Lamp', 'Cable Organizer',
            'Microphone', 'Ring Light', 'External SSD', 'NAS 4-bay',
            'Wi-Fi 6 Router', 'Mesh Access Point', 'Smart Plug', 'HDMI Switch',
            'KVM Switch', 'Mousepad XL', 'Wrist Rest', 'USB Microscope',
            'Bluetooth Speaker', 'VR Headset',
        ];

        foreach ($base as $i => $name) {
            for ($j = 1; $j < 100; $j++) {
                $sku = sprintf('PRD-%03d-%03d', $i + 1, $j);
                Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name'        => $name,
                        'sku'         => $sku,
                        'price'       => round(mt_rand(1500, 60000) / 100, 2),
                        'stock'       => random_int(10, 300),
                        'description' => 'Demo product seeded for the hiring task.',
                    ],
                );
            }
        }
    }
}
