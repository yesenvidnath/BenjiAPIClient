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
        Schema::create('expenses_list', function (Blueprint $table) {
            $table->id('expenseslist_ID');
            $table->unsignedBigInteger('reason_ID');
            $table->foreign('reason_ID')->references('reason_ID')->on('reasons');

            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();

            // Correct foreign key definition
            $table->unsignedBigInteger('expenses_ID'); // Ensure this matches the type in the 'expenses' table
            $table->foreign('expenses_ID')->references('expenses_ID')->on('expenses'); // Ensure this points to the correct column

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses_list');
    }
};
