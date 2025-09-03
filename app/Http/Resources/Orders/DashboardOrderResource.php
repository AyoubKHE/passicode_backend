<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order_items = $this->orderItems->map(function ($item) {

            $impot = (float) $item->price * 5 / 100;
            $chargily = 0;
            if ($item->price < 1000) {
                $chargily = 12.5;
            } else if ($item->price >= 1000 && $item->price <= 100000) {
                $chargily = (float) $item->price * 1.25 / 100;
            } else if ($item->price > 100000) {
                $chargily = 1250;
            }

            return [
                "product" => [
                    "id" => $item->product->id,
                    "code" => Crypt::decryptString($item->product->code),
                    "status" => $item->product->status,
                    "sold" => $item->product->sold,
                    "expiration_date" => $item->product->expiration_date === "9999-12-31" ? null : $item->product->expiration_date,
                    "purchase_price" => $item->product->purchase_price,
                    "supplier" => $item->product->supplier,
                ],
                "price" => $item->price,
                "discount" => $item->discount,
                "unit_profit" => (float) $item->price - (float) $impot - (float) $chargily - (float) $item->product->purchase_price,
            ];
        });

        $total_profit = $order_items->sum(function ($item) {
            return $item['unit_profit'];
        });



        return [
            "id" => $this->id,
            "public_id" => $this->public_id,
            "user" => [
                "id" => $this->user->id,
                "first_name" => $this->user->first_name,
                "last_name" => $this->user->last_name
            ],
            "category" => [
                "id" => $this->category->id,
                "name" => $this->category->name,
                "image_url" => Storage::url($this->category->image_path),
            ],
            "quantity" => $this->quantity,
            "status" => $this->status,
            "type" => $this->type,
            "amount" => $this->amount,
            "more_informations" => $this->more_informations,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "chargily_payment" => [
                "chargily_payment_id" => $this->chargilyPayment->chargily_payment_id,
                "status" => $this->chargilyPayment->status,
            ],
            "order_items" => $order_items,
            "total_profit" => $total_profit,
        ];
    }
}
