<?php

namespace App\Enum;

enum StockMovementType: string
{
    case STOCK_IN = 'stock_in';
    case STOCK_OUT = 'stock_out';
    case RESERVATION = 'reservation';
    case RESERVATION_RELEASE = 'reservation_release';
}
