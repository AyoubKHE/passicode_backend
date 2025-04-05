<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "products";

    protected $fillable = [
        "id",
        "code",
        "sold",
        "expiration_date",
        "created_at",
        "updated_at",
    ];

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            "products_categories",
            "product_id",
            "category_id",
            "id",
            "id"
        );
    }
}
