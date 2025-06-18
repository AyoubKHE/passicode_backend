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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string("first_name", 30)->nullable(false);
            $table->string("last_name", 30)->nullable(false);
            $table->string("email", 255)->nullable(false);
            $table->string("image_url", 255)->nullable(true);
            $table->enum('role', ["Super Admin", "Admin", "Client"])->nullable(false);
            $table->boolean("is_active")->nullable(false);

            $table->string('refresh_token')->nullable(true);

            $table->timestamp('last_login')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
