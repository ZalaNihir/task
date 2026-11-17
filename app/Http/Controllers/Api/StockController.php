<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LowStockRequest;
use App\Http\Requests\MovementHistoryRequest;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Resources\InventorySummaryResource;
use App\Http\Resources\LowStockResource;
use App\Http\Resources\MovementHistoryResource;
use App\Http\Resources\StockAdjustmentResource;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function adjust(StockAdjustmentRequest $request)
    {
        $stock = $this->stockService->adjust($request->validated());

        return new StockAdjustmentResource($stock);
    }

    public function summary()
    {
        $summary = $this->stockService->summary();

        return InventorySummaryResource::collection($summary);
    }

    public function history(MovementHistoryRequest $request,$id)
    {
        $result = $this->stockService->movementHistory(
            $id,
            $request->validated()
        );
        Log::info('Movements-----',[$result['movements']]);
        return MovementHistoryResource::collection($result['movements'])
            ->additional([
                'summary' => [
                    'total_units_in' => $result['summary']['total_units_in'],
                    'total_units_out' => $result['summary']['total_units_out'],
                    'net_movement' => $result['summary']['net_movement'],
                ],
            ]);
    }

    public function lowStock(LowStockRequest $request){
        $threshold = $request->validated()['threshold'] ?? 10;
        $stocks = $this->stockService->lowStock($threshold);

        return LowStockResource::collection($stocks);
    }
}
