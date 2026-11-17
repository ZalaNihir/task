<?php

namespace App\Services;

use App\Enum\StockMovementType;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function adjust($data)
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw ValidationException::withMessages([
                    'stock' => 'Stock record not found for this product and warehouse.',
                ]);
            }

            $quantity = (int) $data['quantity'];
            $availableQuantity = $stock->quantity - $stock->reserved_quantity;
            $movementType = StockMovementType::from($data['movement_type']);

            switch ($movementType) {
                case StockMovementType::STOCK_IN:
                    $stock->quantity += $quantity;
                    break;
                case StockMovementType::STOCK_OUT:
                    if ($availableQuantity < $quantity) {
                        throw ValidationException::withMessages([
                            'quantity' => 'Insufficient available stock for this operation.',
                        ]);
                    }
                    $stock->quantity -= $quantity;
                    break;
                case StockMovementType::RESERVATION:
                    if ($availableQuantity < $quantity) {
                        throw ValidationException::withMessages([
                            'quantity' => 'Insufficient available stock for reservation.',
                        ]);
                    }
                    $stock->reserved_quantity += $quantity;
                    break;
                case StockMovementType::RESERVATION_RELEASE:
                    if ($stock->reserved_quantity < $quantity) {
                        throw ValidationException::withMessages([
                            'quantity' => 'Release quantity cannot exceed reserved stock.',
                        ]);
                    }
                    $stock->reserved_quantity -= $quantity;
                    break;
            }
            $stock->save();
            StockMovement::create([
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'reference_id' => $data['reference_id'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'note' => $data['note'] ?? null,
                'moved_at' => now(),
            ]);

            return $stock;
        });
    }
}
