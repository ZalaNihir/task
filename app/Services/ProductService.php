<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

class ProductService
{
    public function index($data)
    {
        $query = Product::with(['category', 'stocks']);

        if (isset($data['category_id'])) {
            $categoryIds = Category::where('parent_id', $data['category_id'])->pluck('id')->toArray();

            $categoryIds[] = $data['category_id'];

            $query->whereIn('category_id', $categoryIds);
        }

        if (isset($data['warehouse_id'])) {
            $query->whereHas('stocks', function ($q) use ($data) {
                $q->where('warehouse_id', $data['warehouse_id']);
            });
        }

        if (!empty($data['availability'])) {
            $query->whereHas('stocks', function ($q) use ($data) {
                if (isset($data['warehouse_id'])) {
                    $q->where('warehouse_id', $data['warehouse_id']);
                }

                $q->whereRaw('quantity > reserved_quantity');
            });
        }

        if (isset($data['min_price'])) {
            $query->where('base_price', '>=', $data['min_price']);
        }

        if (isset($data['max_price'])) {
            $query->where('base_price', '<=', $data['max_price']);
        }

        if (!empty($data['search'])) {
            $search = $data['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (isset($data['sort_by'])) {
            $sortOrder = $data['sort_order'] ?? 'desc';

            if ($data['sort_by'] === 'name') {
                $query->orderBy('name', $sortOrder);
            } elseif ($data['sort_by'] === 'price') {
                $query->orderBy('base_price', $sortOrder);
            }
        } else {
            $query->latest('id');
        }

        $products = $query->simplePaginate($data['per_page'] ?? 10);

        return $products;
    }

    public function store($data)
    {
        return Product::create($data);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);

        return $product;
    }

    public function update($data, $id){
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product->fresh();
    }
    public function delete($id){
        $product = Product::findOrFail($id);

        $product->delete();
    }
}
