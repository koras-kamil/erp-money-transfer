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
    Schema::create('capitals', function (Blueprint $table) {
        $table->id();
        
        // The Owner (Shareholder)
        $table->foreignId('owner_id')->constrained('users')->onDelete('cascade'); 
        
        // Share Percentage (Must strictly not exceed 100 total)
        $table->decimal('share_percentage', 5, 2); 
        
        // Money Details
        $table->decimal('amount', 20, 3); // Amount in local currency
        $table->foreignId('currency_id')->constrained('currency_configs'); // Currency Type
        $table->decimal('exchange_rate', 20, 3); // The rate at the moment of adding
        $table->decimal('balance_usd', 20, 3); // Calculated Balance in USD
        
        // Meta
        $table->date('date');
        $table->foreignId('created_by')->constrained('users'); // The logged-in user who added this
        
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capitals');
    }
};
