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
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            
            // The Accounts
            $table->foreignId('from_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('accounts')->onDelete('cascade');
            
            // The Currencies
            $table->foreignId('from_currency_id')->constrained('currency_configs')->onDelete('cascade');
            $table->foreignId('to_currency_id')->constrained('currency_configs')->onDelete('cascade');
            
            // The Math
            $table->decimal('amount_sent', 20, 2);
            $table->decimal('amount_received', 20, 2);
            $table->decimal('exchange_rate', 20, 6)->nullable();
            
            // Details
            $table->dateTime('manual_date');
            $table->string('statement_id')->nullable();
            $table->string('note')->nullable();
            
            // Giver & Receiver (From your Excel)
            $table->string('giver_name')->nullable();
            $table->string('giver_phone')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            
            // System Tracking
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // In case you want to safely delete transfers later
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
    }
};
