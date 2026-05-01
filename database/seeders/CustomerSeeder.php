<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Alice Brown',   'email' => 'alice@example.com',   'phone' => '+1-202-555-0101'],
            ['name' => 'Bob Smith',     'email' => 'bob@example.com',     'phone' => '+1-202-555-0102'],
            ['name' => 'Carol Davies',  'email' => 'carol@example.com',   'phone' => '+1-202-555-0103'],
            ['name' => 'David Wilson',  'email' => 'david@example.com',   'phone' => '+1-202-555-0104'],
            ['name' => 'Eva Müller',    'email' => 'eva@example.com',     'phone' => '+49-30-1234567'],
        ];

        foreach ($customers as $c) {
            Customer::updateOrCreate(['email' => $c['email']], $c);
        }
    }
}
