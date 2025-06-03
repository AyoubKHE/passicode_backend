<?php

namespace App\Models\Orders;

use App\Models\Orders\Order;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = "ordersItems";

    public $timestamps = false;

    protected $fillable = [
        "order_id",
        "product_id",
        "price",
        "discount",
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, "order_id", "id");
    }

    public function product()
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }

    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('order_id', $this->getAttribute('order_id'))
            ->where('product_id', $this->getAttribute('product_id'));
        return $query;
    }
}
