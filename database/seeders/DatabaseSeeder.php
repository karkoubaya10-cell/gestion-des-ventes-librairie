<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@test.com'], [
            'name' => 'Administrateur', 'password' => 'Admin123', 'role' => 'admin',
        ]);
        User::updateOrCreate(['email' => 'caissier@test.com'], [
            'name' => 'Caissier', 'password' => 'Caissier123', 'role' => 'caissier',
        ]);

        $products = [
            ['nom' => 'Cahier 100 pages', 'code_barres' => '611000000001', 'prix' => 15.00],
            ['nom' => 'Crayon HB', 'code_barres' => '611000000002', 'prix' => 3.00],
            ['nom' => 'RÃ¨gle 30 cm', 'code_barres' => '611000000003', 'prix' => 7.00],
            ['nom' => 'Roman', 'code_barres' => '611000000004', 'prix' => 80.00],
            ['nom' => 'Stylo bleu', 'code_barres' => '611000000005', 'prix' => 5.00],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['code_barres' => $product['code_barres']], $product);
        }
    }
}

