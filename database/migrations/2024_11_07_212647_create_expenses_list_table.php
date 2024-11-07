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
            $table->foreignId('reason_ID')->constrained('reasons', 'reason_ID'); // Explicitly reference reason_ID
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
