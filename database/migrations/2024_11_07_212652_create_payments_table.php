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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_ID');
            $table->dateTime('datetime');
            $table->decimal('amount', 10, 2);
            $table->foreignId('user_ID')->constrained('users', 'user_ID');
            $table->foreignId('meeting_ID')->constrained('meetings', 'meeting_ID');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
