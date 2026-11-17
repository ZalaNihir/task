<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventorySummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'sku' => $this->sku,
            'total_quantity' => (int) $this->total_quantity,
            'total_reserved_quantity' => (int) $this->total_reserved_quantity,
            'available_quantity' => (int) $this->available_quantity,
            'highest_stock_warehouse' => $this->highest_stock_warehouse
                ? [
                    'name' => $this->highest_stock_warehouse,
                ]
                : null,
            'is_out_of_stock' => (int) $this->total_quantity === 0,
        ];
    }
}
