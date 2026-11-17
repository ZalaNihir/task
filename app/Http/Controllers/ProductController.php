<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService){}
    public function index(ProductIndexRequest $request)
    {
        $validate = $request->validated();
        $products = $this->productService->index($validate);

        return ProductResource::collection($products);
    }

    public function store(ProductFormRequest $request)
    {
        $validate = $request->validated();
        $product = $this->productService->store($validate);

        return new ProductResource($product);
    }
    public function show($id)
    {
        $product = $this->productService->show($id);

        return new ProductResource($product);
    }
    public function update(ProductFormRequest $request, $id)
    {
        $validate = $request->validated();
        $product = $this->productService->update($validate, $id);

        return new ProductResource($product);
    }
    public function destroy($id){
        $this->productService->delete($id);

        return response()->json([
            'message' => 'Product deleted.'
        ]);
    }
}
