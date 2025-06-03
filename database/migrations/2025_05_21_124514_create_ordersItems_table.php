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
        Schema::create('ordersItems', function (Blueprint $table) {

            $table->unsignedBigInteger('order_id')->nullable(false);
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->nullable(false);
            $table->foreign('product_id')
                ->references('id')
                ->on('products');

            $table->decimal('price', 10, 2)->nullable(false);
            $table->unsignedSmallInteger("discount")->nullable(true);

            $table->primary(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordersItems');
    }
};
