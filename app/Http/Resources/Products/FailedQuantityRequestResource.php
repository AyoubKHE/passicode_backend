<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FailedQuantityRequestResource extends JsonResource
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
            "category" => [
                'id' => $this->category->id,
                'name' => $this->category->name
            ],
            "user" => [
                'id' => $this->user->id,
                'full_name' => $this->user->first_name . ' ' . $this->user->last_name,
            ],
            "available_quantity" => $this->available_quantity,
            "requested_quantity" => $this->requested_quantity,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "settled_at" => $this->settled_at
        ];
    }
}
