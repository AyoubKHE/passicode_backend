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
        Schema::create('chargilyPayments', function (Blueprint $table) {
            $table->id();

            $table->string("chargily_payment_id", 50)->nullable(true)->unique();

            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('order_id')->nullable(false);
            $table->foreign('order_id')->references('id')->on('orders');

            $table->enum("status", ["pending", "paid", "failed", "canceled", "expired"])->default("pending");
            $table->string("currency");
            $table->string("amount");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargilyPayments');
    }
};
