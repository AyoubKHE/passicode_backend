<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "code" => Crypt::decryptString($this->code),
            "sold" => $this->sold,
            "expiration_date" => $this->expiration_date,
            "purchase_price" => $this->purchase_price,
            "categories" => $this->relationLoaded('categories') ? $this->categories : [],
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
