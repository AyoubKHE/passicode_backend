<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->unsignedInteger("id", true);

            $table->string("name", 255)->nullable(false)->unique("idx_categories_name");
            $table->text("description")->nullable(false)->unique();
            $table->decimal('price', 10, 2)->nullable(true);
            $table->unsignedSmallInteger("discount")->nullable(true);
            $table->unsignedInteger("quantity")->nullable(false)->default(0);
            $table->string("image_path", 255)->nullable(false);
            $table->boolean("is_active")->nullable(false);
            $table->boolean("is_leaf_category")->nullable(false);
            $table->unsignedInteger('parent_id')->nullable(true)
                ->index("idx_categories_parent_id");
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();

            $table->timestamp('created_at')->nullable(false);
            $table->timestamp('updated_at')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
