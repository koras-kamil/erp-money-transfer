<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('receivings', function (Blueprint $table) {
            $table->id(); // Auto-increment ID (Also serves as Invoice ID)
            
            $table->string('statement_id')->nullable(); // Manual Input
            $table->dateTime('manual_date')->nullable(); // User selected date
            
            $table->string('invoice_type')->default('normal'); // Normal, Official, etc.
            
            // Relationships
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currency_configs'); // Assuming 'currency_configs' is your table
            $table->foreignId('cashbox_id')->constrained('cash_boxes');
            $table->foreignId('user_id')->constrained('users');

            // Financials
            $table->decimal('amount', 20, 2); // The Cash Amount
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('exchange_rate', 20, 4)->default(1);

            // Details
            $table->string('giver_name')->nullable();
            $table->string('giver_mobile')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_mobile')->nullable();
            $table->text('note')->nullable();

            $table->timestamps(); // System Date & Time (created_at)
        });
    }

    public function down()
    {
        Schema::dropIfExists('receivings');
    }
};