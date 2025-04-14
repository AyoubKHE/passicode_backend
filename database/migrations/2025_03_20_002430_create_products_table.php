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

            $table->string("code", 255)->nullable(false)->unique();
            $table->string("code_start", 5)->nullable(false);
            $table->boolean("sold")->nullable(false);
            $table->string('expiration_date', 50)->nullable(true);
            $table->decimal('purchase_price', 10, 2)->nullable(false);

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
