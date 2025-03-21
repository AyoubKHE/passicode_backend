<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product_Category extends Model
{
    use HasFactory;

    protected $table = "products_categories";

    public $timestamps = false;

    protected $fillable = [
        "product_id",
        "category_id",
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id", "id");
    }

    public function product()
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }

    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('product_id', $this->getAttribute('product_id'))
            ->where('category_id', $this->getAttribute('category_id'));
        return $query;
    }
}
