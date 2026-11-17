<?php

namespace App\Services;

use App\Enum\StockMovementType;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    protected $chachekey = 'summery';
    protected $ttl = 300;
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
            Cache::forget($this->chachekey);

            return $stock;
        });
    }
    public function summary()
    {
        $cachedSummary = Cache::get($this->chachekey);

        if ($cachedSummary !== null) {
            return $cachedSummary;
        }

        $summary = Product::query()
            ->leftJoin('stocks', 'products.id', '=', 'stocks.product_id')
            ->select(
                'products.id',
                'products.name',
                'products.sku'
            )
            ->selectRaw('COALESCE(SUM(stocks.quantity), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(stocks.reserved_quantity), 0) as total_reserved_quantity')
            ->selectRaw('COALESCE(SUM(stocks.quantity - stocks.reserved_quantity), 0) as available_quantity')
            ->selectSub(
                DB::table('stocks as highest_stocks')
                    ->join('warehouses', 'highest_stocks.warehouse_id', '=', 'warehouses.id')
                    ->whereColumn('highest_stocks.product_id', 'products.id')
                    ->orderByDesc('highest_stocks.quantity')
                    ->orderBy('highest_stocks.id')
                    ->limit(1)
                    ->select('warehouses.name'),
                'highest_stock_warehouse'
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->get();

        if ($summary->isNotEmpty()) {
            Cache::put($this->chachekey,$summary,now()->addSeconds($this->ttl));
        }

        return $summary;
    }

}
