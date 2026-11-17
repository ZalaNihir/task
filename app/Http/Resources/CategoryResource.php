<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
            'children'  => $this->when(
                $request->routeIs('categories.tree', 'categories.show'),
                fn () => CategoryResource::collection($this->whenLoaded('children'))
            ),
            'parent'    => $this->when(
                $request->routeIs('categories.show'),
                fn () => new CategoryResource($this->whenLoaded('parent'))
            ),
        ];
    }
}
