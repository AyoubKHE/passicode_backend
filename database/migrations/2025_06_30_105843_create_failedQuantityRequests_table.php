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
        Schema::create('failedQuantityRequests', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('category_id')->nullable(false);
            $table->foreign('category_id')->references('id')->on('categories');

            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedInteger("available_quantity")->nullable(false);
            $table->unsignedInteger("requested_quantity")->nullable(false);

            $table->boolean("is_category_active")->nullable(false);

            $table->enum("status", ["not_settled", "settled"])->default("not_settled");

            $table->timestamp('created_at')->nullable(false);
            $table->timestamp('settled_at')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failedQuantityRequests');
    }
};
