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
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedInteger("id", true);

            $table->string("name", 255)->nullable(false)->unique();
            $table->text("description")->nullable(false)->unique();
            $table->decimal('price', 10, 2)->nullable(false);
            $table->unsignedSmallInteger("discount")->nullable(true);
            $table->string("image_path", 255)->nullable(false);
            $table->boolean("is_active")->nullable(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
