<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        //

        \App\Models\Hpp::create([
            'description' => 'Pembelian',
            'date' => Carbon::CreateFromFormat('d/m/Y', '01/01/2021'),
            'qty' => 40,
            'cost' => 100,
            'price' => 100,
            'total_cost' => 4000,
            'qty_balance' => 40,
            'value_balance' => 4000,
            'hpp' => 100
        ]);

        \App\Models\Hpp::create([
            'description' => 'Penjualan',
            'date' => Carbon::CreateFromFormat('d/m/Y', '01/01/2021'),
            'qty' => -20,
            'cost' => 100,
            'price' => 200,
            'total_cost' => -2000,
            'qty_balance' => 20,
            'value_balance' => 2000,
            'hpp' => 100
        ]);

        // \App\Models\Hpp::create([
        //     'description' => 'Pe',
        //     'date' => Carbon::CreateFromFormat('d/m/Y', '05/01/2021'),
        //     'qty' => 10,
        //     'cost' => 200,
        //     'price' => 200,
        //     'total_cost' => 2000,
        //     'qty_balance' => 70,
        //     'value_balance' => 8400,
        //     'hpp' => 8400/70
        // ]);

        \App\Models\Hpp::create([
            'description' => 'Pembelian',
            'date' => Carbon::CreateFromFormat('d/m/Y', '03/01/2021'),
            'qty' => 20,
            'cost' => 120,
            'price' => 120,
            'total_cost' => 2400,
            'qty_balance' => 40,
            'value_balance' => 4400,
            'hpp' => 110
        ]);

        // \App\Models\Hpp::create([
        //     'description' => 'Penjualan',
        //     'date' => Carbon::CreateFromFormat('d/m/Y', '05/01/2021'),
        //     'qty' => 10,
        //     'cost' => 137.78,
        //     'price' => 100,
        //     'total_cost' => -1377.8,
        //     'qty_balance' => 90+-(10),
        //     'value_balance' => 12400,
        //     'hpp' => 12400/90
        // ]);
    }
}
