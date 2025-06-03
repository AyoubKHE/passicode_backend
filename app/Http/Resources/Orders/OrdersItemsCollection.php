<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use App\Http\Resources\Orders\OrderItemResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrdersItemsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_items' => OrderItemResource::collection($this->collection),
        ];
    }
}
