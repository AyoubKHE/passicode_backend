<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleOrderResource extends JsonResource
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
            "public_id" => $this->public_id,
            "user" => [
                'id' => $this->user->id,
                'full_name' => $this->user->first_name . ' ' . $this->user->last_name,
            ],
            "status" => $this->status,
            "amount" => $this->amount,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
