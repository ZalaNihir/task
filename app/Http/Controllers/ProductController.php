<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->latest('id')
            ->simplePaginate($request->get('per_page', 10));

        return ProductResource::collection($products);
    }
}
