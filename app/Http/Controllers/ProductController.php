<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request)
    {
        $validate = $request->validated();

        $query = Product::with(['category', 'stocks']);

        if (isset($validate['category_id'])) {
            $categoryIds = Category::where('parent_id', $validate['category_id'])
                ->pluck('id')
                ->toArray();

            $categoryIds[] = $validate['category_id'];

            $query->whereIn('category_id', $categoryIds);
        }

        if (isset($validate['warehouse_id'])) {
            $query->whereHas('stocks', function ($q) use ($validate) {
                $q->where('warehouse_id', $validate['warehouse_id']);
            });
        }

        if (! empty($validate['availability'])) {
            $query->whereHas('stocks', function ($q) use ($validate) {
                if (isset($validate['warehouse_id'])) {
                    $q->where('warehouse_id', $validate['warehouse_id']);
                }

                $q->whereRaw('quantity > reserved_quantity');
            });
        }

        if (isset($validate['min_price'])) {
            $query->where('base_price', '>=', $validate['min_price']);
        }

        if (isset($validate['max_price'])) {
            $query->where('base_price', '<=', $validate['max_price']);
        }

        if (! empty($validate['search'])) {
            $search = $validate['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (isset($validate['sort_by'])) {
            $sortOrder = $validate['sort_order'] ?? 'desc';

            if ($validate['sort_by'] === 'name') {
                $query->orderBy('name', $sortOrder);
            } elseif ($validate['sort_by'] === 'price') {
                $query->orderBy('base_price', $sortOrder);
            }
        } else {
            $query->latest('id');
        }

        $products = $query->simplePaginate($validate['per_page'] ?? 10);

        return ProductResource::collection($products);
    }
}
