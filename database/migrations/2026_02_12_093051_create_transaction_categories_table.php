<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. NEW TABLE: Stores "Rent", "Salary", "FastPay", etc.
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // The name (e.g., "Office Rent")
            $table->enum('type', ['profit', 'spending']); // Is it Money IN or Money OUT?
            
            // 🟢 Links to your EXISTING currency_configs table
            // This ensures "Rent" is always USD, or "Salaries" are always IQD
            $table->foreignId('currency_id')->constrained('currency_configs'); 
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. UPDATE EXISTING TABLE: Connects Transactions to this new list
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('transaction_categories');
            }
            if (!Schema::hasColumn('transactions', 'is_debt')) {
                $table->boolean('is_debt')->default(false); // The Debt Checkbox
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'is_debt']);
        });
        Schema::dropIfExists('transaction_categories');
    }
};