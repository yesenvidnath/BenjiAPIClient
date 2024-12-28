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
        Schema::create('weekly_chart_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_ID')->constrained('users', 'user_ID');
            $table->enum('day_name', ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7']);
            $table->decimal('expense', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('weekly_chart_data');
    }
};
