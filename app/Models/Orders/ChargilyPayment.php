<?php

namespace App\Models\Orders;

use App\Models\Users\User;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChargilyPayment extends Model
{
    use HasFactory;

    protected $table = "chargilyPayments";

    public $timestamps = false;

    protected $fillable = [
        "id",
        "chargily_payment_id",
        "user_id",
        "order_id",
        "status",
        "currency",
        "amount",
        "created_at",
        "updated_at"
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, "order_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
}
