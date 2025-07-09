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
            $table->id();

            $table->unsignedInteger('category_id')->nullable(false);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories');

            $table->string("code", 255)->nullable(false)->unique();

            $table->string("code_start", 5)->nullable(false);

            $table->boolean("sold")->nullable(false);

            $table->enum("status", ["valid", "expired", "used", "unexisting", "under_review"])->nullable(false)->default("valid");

            $table->date('expiration_date')->nullable(false);

            $table->decimal('purchase_price', 10, 2)->nullable(false);

            $table->string("supplier", 255)->nullable(true);

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
