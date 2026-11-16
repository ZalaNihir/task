<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Rajkamal',
                'city' => 'Rajkot',
                'is_active' => true,
            ],
            [
                'name' => 'Ahmedpuri',
                'city' => 'Ahmedabad',
                'is_active' => true,
            ],
            [
                'name' => 'VeriFied',
                'city' => 'Vadodara',
                'is_active' => true,
            ],
            [
                'name' => 'Surya',
                'city' => 'Surat',
                'is_active' => true,
            ],
            [
                'name' => 'Lakhota',
                'city' => 'Jamanagr',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(
                [
                    'name' => $warehouse['name'],
                    'city' => $warehouse['city'],
                ],
                [
                    'is_active' => $warehouse['is_active'],
                ]
            );
        }
    }
}
