<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_ID'); // Primary key is explicitly named 'user_ID'
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->enum('type', ['Customer', 'Professional', 'Admin',]);
            $table->date('DOB')->nullable();
            $table->string('phone_number', 20);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->string('bank_choice')->nullable();
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
