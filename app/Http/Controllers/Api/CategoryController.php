<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryFormRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService){}

    public function index(IndexRequest $request){
        $validate = $request->validated();
        $categories = $this->categoryService->index($validate['per_page'] ?? 10);

        return CategoryResource::collection($categories);
    }

    public function store(CategoryFormRequest $request){
        $validate = $request->validated();
        $category = $this->categoryService->store($validate);

        return new CategoryResource($category);
    }

    public function show($id){
        $data = $this->categoryService->show($id);

        return new CategoryResource($data);
    }

    public function update(CategoryFormRequest $request,$id){
        $validate = $request->validated();
        $category = $this->categoryService->update($validate,$id);

        return new CategoryResource($category);

    }
    public function destroy($id){
        $this->categoryService->delete($id);

        return response()->json([
            'message' => 'Category deleted.'
        ], 200);
    }

    public function tree()
    {
        $categoryTree = $this->categoryService->tree();

        return CategoryResource::collection($categoryTree);
    }
}
