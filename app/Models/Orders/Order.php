<?php

namespace App\Models\Orders;

use App\Models\Products\Category;
use App\Models\Users\User;
use App\Models\Orders\OrderItem;
use App\Models\Orders\ChargilyPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    public $timestamps = false;

    protected $fillable = [
        "id",
        "public_id",
        "user_id",
        "category_id",
        "quantity",
        "status",
        "type",
        "amount",
        "more_informations",
        "created_at",
        "updated_at"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id", "id");
    }

    public function chargilyPayment()
    {
        return $this->hasOne(ChargilyPayment::class, "order_id", "id");
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, "order_id", "id");
    }
}
