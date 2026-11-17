<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;

class StockSeeder extends Seeder
{
    public function run()
    {
        $productIds = Product::pluck('id');
        $warehouseIds = Warehouse::pluck('id');

        foreach ($productIds as $productId) {
            foreach ($warehouseIds as $warehouseId) {
                $quantity = rand(10, 100);
                $reserved = rand(1, 9);

                Stock::updateOrCreate([
                    'product_id'   => $productId,
                    'warehouse_id' => $warehouseId,
                ],[
                    'quantity'     => $quantity,
                    'reserved_quantity' => $reserved,
                ]);
            }
        }
    }
}
