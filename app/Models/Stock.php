<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = [
        'product_id','warehouse_id','quantity','reserved_quantity'
    ];
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
        ];
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')->where('warehouse_id', $this->warehouse_id);
    }
}
