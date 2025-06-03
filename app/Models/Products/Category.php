<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = "categories";

    public $timestamps = false;

    protected $fillable = [
        "id",
        "name",
        "description",
        "price",
        "discount",
        "quantity",
        "image_path",
        "is_active",
        "is_leaf_category",
        "parent_id",
        "created_at",
        "updated_at"
    ];

    // protected $hidden = [
    //     'pivot',
    // ];

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, "parent_id", "id");
    }

    public function childCategories()
    {
        return $this->hasMany(Category::class, "parent_id", "id");
    }

    public function products()
    {
        return $this->hasMany(Product::class, "category_id", "id");
    }
}
