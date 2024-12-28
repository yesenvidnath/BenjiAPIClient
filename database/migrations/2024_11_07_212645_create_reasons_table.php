<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('reasons', function (Blueprint $table) {
            $table->id('reason_ID'); // Ensure this is the primary key
            $table->string('reason'); // Initially create as a simple string field
            $table->foreignId('category_ID')->constrained('categories', 'category_ID');
            $table->timestamps();
        });

        // Alter the column to add ENUM values
        DB::statement("ALTER TABLE reasons MODIFY reason ENUM(
            'Tuition fee payment for a school or college',
            'Purchase of textbooks and study materials',
            'Online course or workshop fee',
            'Payment for car service or maintenance',
            'Purchase of spare parts for a vehicle',
            'Payment for a car wash',
            'Purchase of party supplies (decorations, balloons)',
            'Payment for event tickets (concerts, festivals)',
            'Renting a venue for a party',
            'Purchase of vegetables and fruits from a local market',
            'Payment for a meal at a restaurant',
            'Buying snacks and beverages from a convenience store',
            'Monthly rent payment',
            'Electricity bill payment',
            'Water bill payment',
            'Fuel purchase for a car',
            'Payment for a public transport ticket (bus, train)',
            'Ride-sharing service payment (e.g., Uber, Lyft)',
            'Purchase of toiletries and personal care products',
            'Payment for a doctor''s appointment',
            'Buying prescription medicines from a pharmacy',
            'Subscription fee for a streaming service (e.g., Netflix, Spotify)',
            'Payment for a movie ticket',
            'Purchase of a book or magazine',
            'Initial down payment for an installment plan (e.g., electronics)',
            'Monthly installment payment for a purchased item',
            'Payment for a financed home appliance',
            'Monthly premium payment for life insurance',
            'Payment for health insurance premiums',
            'Contribution to a retirement fund',
            'Monthly mortgage payment',
            'Payment for a personal loan installment',
            'Credit card bill payment'
        )");
    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reasons');
    }
};
