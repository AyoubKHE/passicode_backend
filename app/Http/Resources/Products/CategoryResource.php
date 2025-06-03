<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "price" => $this->price,
            "discount" => $this->discount,
            "quantity" => $this->quantity,
            "category_image_url" => Storage::url($this->image_path),
            "is_active" => $this->is_active,
            "is_leaf_category" => $this->is_leaf_category,
            "parent" => $this->parentCategory ? [
                'id' => $this->parentCategory->id,
                'name' => $this->parentCategory->name
            ] : null,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
