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
        Schema::create('professionals', function (Blueprint $table) {
            $table->foreignId('user_ID')->primary()->constrained('users', 'user_ID'); // Explicitly reference 'user_ID' in 'users'
            $table->enum('status', ['pending', 'active', 'banned', 'suspended']);
            $table->enum('type', [
                'Accountant',
                'Financial Advisor',
                'Stock Broker',
                'Banker',
                'Insurance Agent',
                'Investment Specialist',
                'Tax Consultant',
                'Real Estate Agent',
                'Loan Officer',
                'Wealth Manager',
                'Mortgage Advisor',
                'Retirement Planner',
                'Business Consultant',
                'Other' // 'Other' type option
            ]);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
