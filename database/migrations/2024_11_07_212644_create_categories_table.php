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
        Schema::create('categories', function (Blueprint $table) {
            $table->id('category_ID'); // Ensure primary key is set here as 'category_ID'
            $table->enum('category', [
                'Groceries and Food', 'Housing and Utilities', 'Transportation',
                'Personal Care and Health', 'Entertainment and Leisure',
                'Education', 'Vehicle Repair and Maintenance', 'Party and Fun',
                'Part Payment Purchases', 'Life Insurance and Financial Services',
                'Loans and Credit'
            ]);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
