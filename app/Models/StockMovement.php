<?php

namespace App\Models;

use App\Enum\StockMovementType;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';
    public $timestamps = false;

    protected $fillable = [
        'product_id','warehouse_id','movement_type','quantity','reference_id','reference_type','note','moved_at',
    ];

    protected function casts()
    {
        return [
            'movement_type' => StockMovementType::class,
            'quantity' => 'integer',
            'moved_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
