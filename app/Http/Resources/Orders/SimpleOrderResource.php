<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleOrderResource extends JsonResource
{
    private float $tax;
    private float $payment_gateway;

    private function calculateUnitProfit(
        float $supplier_price,
        float $customer_price
    ): float {

        $gateway_fee = 0.0;

        if ($this->payment_gateway !== 0.0) {
            $payment_gateway_configs = [
                1.25 => [
                    'percentage' => 0.0125,
                    'low_fixed' => 12.5,
                    'high_fixed' => 1250,
                ],
                2.5 => [
                    'percentage' => 0.025,
                    'low_fixed' => 25,
                    'high_fixed' => 2500,
                ],
            ];

            $payment_gateway_config = $payment_gateway_configs[$this->payment_gateway];

            if ($customer_price <= 1000) {
                $gateway_fee = $payment_gateway_config['low_fixed'];
            } elseif ($customer_price >= 100000) {
                $gateway_fee = $payment_gateway_config['high_fixed'];
            } else {
                $gateway_fee = $customer_price * $payment_gateway_config['percentage'];
            }
        }

        $tax_amount = $customer_price * $this->tax;

        return round($customer_price - $tax_amount - $gateway_fee - $supplier_price, 2);
    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profit = null;

        if ($this->status === "completed") {

            $this->tax = config('app.TAX');
            $this->payment_gateway = config('app.PAYMENT_GATEWAY');

            $profit = $this->orderItems->sum(function ($item) {

                return $this->calculateUnitProfit(
                    (float) $item->product->purchase_price,
                    (float) $item->price
                );
            });
        }

        return [
            "id" => $this->id,
            "public_id" => $this->public_id,
            "user" => [
                'id' => $this->user->id,
                'full_name' => $this->user->first_name . ' ' . $this->user->last_name,
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
            "profit" => $profit,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
