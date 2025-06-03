<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use App\Http\Resources\Products\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "product" => new ProductResource($this->product),
            "price" => $this->price,
            "discount" => $this->discount,
        ];
    }
}
