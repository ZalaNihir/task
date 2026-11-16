<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function tree()
    {
        $categoryTree = Cache::remember('categories', now()->addHour(), function () {
            $categories = Category::orderBy('name')->get();
            $categoriesByParent = $categories->groupBy('parent_id');
            $buildTree = function ($parentId) use (&$buildTree, $categoriesByParent) {
                $categoryTree = collect();
                foreach ($categoriesByParent->get($parentId, collect()) as $category) {
                    $childCategories = $buildTree($category->id);
                    if ($category->is_active) {
                        $category->setRelation('children', $childCategories);
                        $categoryTree->push($category);
                    } else {
                        $categoryTree = $categoryTree->merge($childCategories);
                    }
                }

                return $categoryTree->sortBy('name')->values();
            };

            return $buildTree(null);
        });

        return CategoryResource::collection($categoryTree);
    }
}
