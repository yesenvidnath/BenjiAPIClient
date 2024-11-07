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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_ID');
            $table->foreignId('user_ID')->constrained('users', 'user_ID'); // Reference to the user
            $table->enum('type', ['meeting', 'payment', 'general'])->default('general'); // Type of notification
            $table->string('message'); // Notification message
            $table->boolean('is_read')->default(false); // Read status of the notification
            $table->timestamps(); // Created and updated timestamps
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
