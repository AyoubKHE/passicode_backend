<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Orders\OrdersItemsCollection;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order_items = $this->orderItems->map(function ($item) {
            return [
                "product" => [
                    "code" => Crypt::decryptString($item->product->code),
                    "expiration_date" => $item->product->expiration_date === "9999-12-31" ? null : $item->product->expiration_date,
                    "category" => [
                        "name" => $item->product->category->name,
                        "image_url" => Storage::url($item->product->category->image_path),
                    ]
                ],
                "price" => $item->price,
                "discount" => $item->discount,
            ];
        });

        return [
            "id" => $this->id,
            "public_id" => $this->public_id,
            "user" => [
                "id" => $this->user->id,
                "first_name" => $this->user->first_name,
                "last_name" => $this->user->last_name
            ],
            "status" => $this->status,
            "amount" => $this->amount,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "chargily_payment" => [
                "chargily_payment_id" => $this->chargilyPayment->chargily_payment_id,
            ],
            "order_items" => $order_items,
        ];
    }
}
