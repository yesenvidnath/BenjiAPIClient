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
        Schema::table('expenses_list', function (Blueprint $table) {
            Schema::table('expenses_list', function (Blueprint $table) {
                // Adding the expenses_id column
                $table->unsignedBigInteger('expenses_id')->nullable(); // Or use 'integer' depending on your setup

                // Adding the foreign key constraint
                $table->foreign('expenses_id')->references('expenses_id')->on('expenses')
                      ->onDelete('cascade'); // You can change the onDelete behavior to 'set null' or 'restrict' based on your needs
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses_list', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['expenses_id']);

            // Drop the expenses_id column
            $table->dropColumn('expenses_id');
        });
    }
};
