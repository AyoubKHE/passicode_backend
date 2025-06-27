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
            "category" => $this->relationLoaded('category') ? $this->category : null,
            "code" => Crypt::decryptString($this->code),
            "sold" => $this->sold,
            "expiration_date" => $this->expiration_date,
            "purchase_price" => $this->purchase_price,
            "supplier" => $this->supplier,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
