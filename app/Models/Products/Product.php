<?php

namespace App\Models\Products;

use App\Models\Products\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "products";

    protected $fillable = [
        "id",
        "category_id",
        "code",
        "code_start",
        "sold",
        "status",
        "expiration_date",
        "purchase_price",
        "supplier",
        "created_at",
        "updated_at",
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id", "id");
    }
}
