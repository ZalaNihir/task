<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryService
{
    protected $categories = 'categories';
    public function index($perPage = 10)
    {
        return Category::simplePaginate($perPage);
    }

    public function store($data)
    {
        $category = Category::create([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'slug' => $this->generateUniqueSlug($data['name']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->deletecached();

        return $category;
    }

    public function show($id)
    {
        return Category::where('id', $id)
            ->with(['children', 'parent'])
            ->first();
    }

    public function update($data, $id)
    {
        $category = Category::findOrFail($id);

        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        $category->update($data);
        $this->deletecached();

        return $category->fresh();
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);

        if ($category->children()->exists() || $category->products()->exists()) {
            throw new \Exception('Cannot delete category because it has subcategories or products.');
        }

        $deleted = $category->delete();

        if ($deleted) {
            $this->deletecached();
        }

        return $deleted;
    }

    public function tree()
    {
        return Cache::remember($this->categories, now()->addHour(), function () {
            $categories = Category::orderBy('name')->get();
            $categoriesByParent = $categories->groupBy('parent_id');

            $data = function ($parentId) use (&$data, $categoriesByParent) {
                $categoryData = collect();

                foreach ($categoriesByParent->get($parentId, collect()) as $category) {
                    $childCategories = $data($category->id);

                    if ($category->is_active) {
                        $category->setRelation('children', $childCategories);
                        $categoryData->push($category);
                    } else {
                        $categoryData = $categoryData->merge($childCategories);
                    }
                }

                return $categoryData->sortBy('name')->values();
            };

            return $data(null);
        });
    }

    private function deletecached(){
        return Cache::forget($this->categories);
    }

    private function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    private function slugExists($slug, $ignoreId = null)
    {
        return Category::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
