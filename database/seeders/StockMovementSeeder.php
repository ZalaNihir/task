<?php

namespace Database\Seeders;

use App\Enum\StockMovementType;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stocks = Stock::select('id', 'product_id', 'warehouse_id')->get();
        $movementTypes = StockMovementType::cases();
        foreach (range(1, 200) as $index) {
            $stock = $stocks->random();

            StockMovement::create([
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_type' => fake()->randomElement($movementTypes),
                'quantity' => fake()->numberBetween(1, 20),
                'reference_id' => null,
                'reference_type' => null,
                'note' => fake()->sentence(),
                'moved_at' => fake()->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}
