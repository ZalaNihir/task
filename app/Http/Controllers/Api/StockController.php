<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Resources\StockAdjustmentResource;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function adjust(StockAdjustmentRequest $request)
    {
        $stock = $this->stockService->adjust($request->validated());

        return new StockAdjustmentResource($stock);
    }

}
