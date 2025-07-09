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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->ulid('public_id')->nullable(false)->unique();

            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->unsignedInteger('category_id')->nullable(false);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories');

            $table->unsignedInteger("quantity")->nullable(false);

            $table->enum("status", ["pending", "processing", "completed", "failed", "under_review", "partially_refunded", "refunded"])->default("pending");

            $table->string("amount");

            $table->enum("type", ["instock", "backorder"])->nullable(false);

            $table->text("more_informations")->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
