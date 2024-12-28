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
        Schema::create('monthly_chart_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_ID')->constrained('users', 'user_ID');
            $table->enum('week_name', ['Week 0', 'Week 1', 'Week 2', 'Week 3', 'Week 4']);
            $table->decimal('expense', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('monthly_chart_data');
    }
};
