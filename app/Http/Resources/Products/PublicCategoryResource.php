<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "category_image_url" => Storage::url($this->image_path),
            "is_leaf_category" => $this->is_leaf_category,
            "parent_id" => $this->parent_id,
        ];

        if ($this->is_leaf_category) {
            $data["description"] = $this->description;
            $data["price"] = $this->price;
            $data["discount"] = $this->discount;
        }

        return $data;
    }
}
