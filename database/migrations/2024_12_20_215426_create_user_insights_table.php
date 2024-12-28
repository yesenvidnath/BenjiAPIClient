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
        Schema::create('user_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_ID')->constrained('users', 'user_ID');
            $table->text('forecasting_message');
            $table->text('insights');
            $table->string('saving_percentage');
            $table->string('spending_percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_insights');
    }
};
