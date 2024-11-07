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
        Schema::create('professionals', function (Blueprint $table) {
            $table->foreignId('user_ID')->primary()->constrained('users', 'user_ID'); // Explicitly reference 'user_ID' in 'users'
            $table->string('certificate_ID');
            $table->enum('status', ['pending', 'active', 'banned', 'suspended']);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
