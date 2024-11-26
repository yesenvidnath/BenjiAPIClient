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
        Schema::create('income_sources', function (Blueprint $table) {
            $table->id('income_source_ID');
            $table->foreignId('user_ID')->constrained('users', 'user_ID'); // Reference to the user
            $table->string('source_name'); // Name of the income source
            $table->decimal('amount', 10, 2); // Amount of income from this source
            $table->enum('frequency', ['monthly', 'yearly', 'daily', 'weekly']); // Income frequency
            $table->text('description')->nullable(); // Optional description for additional details
            $table->timestamps(); // Created and updated timestamps
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_sources');
    }
};
