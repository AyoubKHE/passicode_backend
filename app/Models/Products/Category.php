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
        "image_path",
        "is_active",
        // "show_on_website_header",
        "is_leaf_category",
        "parent_id",
        "created_at",
        "updated_at"
    ];

    protected $hidden = [
        'pivot',
    ];

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
        return $this->belongsToMany(Product::class, "products_categories", "category_id", "product_id", "id", "id");
    }

    // public function productsExcept(int $except_id)
    // {
    //     return $this->belongsToMany(Product::class, "productscategories_products", "productcategory_id", "product_id", "id", "id")
    //         ->withPivot("is_active")
    //         ->with("addedBy")
    //         ->with("brand")
    //         ->with("images")
    //         ->where("products.id", "!=", $except_id)
    //         ->where("productsCategories_products.is_active", 1);
    // }

    // public function productsPivot()
    // {
    //     return $this->hasMany(ProductCategory_Product::class, "productCategory_id", "id");
    // }



    // public static function tree($root)
    // {
    //     $all_categories = ProductCategory::get();

    //     $sub_categories = $all_categories->where("parent_id", $root);

    //     static::formatTree($sub_categories, $all_categories);

    //     return $sub_categories;
    // }

    // private static function formatTree($categories, $all_categories)
    // {
    //     foreach ($categories as $category) {

    //         $category->sub_categories = $all_categories->where("parent_id", $category->id);

    //         static::formatTree($category->sub_categories, $all_categories);

    //     }
    // }
}
