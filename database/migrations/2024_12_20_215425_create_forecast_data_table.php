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
        Schema::create('forecast_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_ID')->constrained('users', 'user_ID');
            $table->decimal('monthly_expense', 10, 2);
            $table->decimal('total_expense', 10, 2);
            $table->decimal('total_income', 10, 2);
            $table->decimal('weekly_expense', 10, 2);
            $table->decimal('yearly_expense', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('forecast_data');
    }
};
