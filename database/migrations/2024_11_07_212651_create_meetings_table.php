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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id('meeting_ID');
            $table->dateTime('time_date');
            $table->foreignId('user_ID_customer')->constrained('users', 'user_ID'); // Reference to users.user_ID
            $table->foreignId('user_ID_professional')->constrained('professionals', 'user_ID'); // Reference to professionals.user_ID
            $table->string('meet_url');
            $table->enum('status', ['canceled', 'rescheduled', 'completed', 'pending']);
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
