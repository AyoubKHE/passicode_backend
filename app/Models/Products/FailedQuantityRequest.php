<?php

namespace App\Models\Products;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FailedQuantityRequest extends Model
{
    use HasFactory;

    protected $table = "failedquantityrequests";

    public $timestamps = false;

    protected $fillable = [
        "id",
        "category_id",
        "user_id",
        "available_quantity",
        "requested_quantity",
        "status",
        "created_at",
        "settled_at"
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
}
