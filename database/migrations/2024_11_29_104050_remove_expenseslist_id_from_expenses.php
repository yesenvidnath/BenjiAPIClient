<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {

            $table->dropForeign(['expenseslist_ID']);  // Drop foreign key
            $table->dropColumn('expenseslist_ID');    // Drop the column
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expenseslist_ID')->constrained('expenses_list', 'expenseslist_ID'); // Re-add foreign key
        });

    }
};
