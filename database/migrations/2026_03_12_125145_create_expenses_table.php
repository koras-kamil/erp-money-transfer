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
    Schema::create('expenses', function (Blueprint $table) {
        $table->id(); // ڕێز
        
        // Document Info
        $table->string('voucher_number')->unique(); // ژمارەی بەڵگە
        $table->string('manual_voucher')->nullable(); // بەڵگەی دەستی
        $table->date('expense_date'); // کات - بەروار
        
        // 🟢 FIXED: Now pointing to your actual table 'type_spendings'
        $table->foreignId('spending_category_id')->constrained('type_spendings'); 
        
        // Amounts
        $table->decimal('cash_amount', 15, 2)->default(0); // نەقد
        $table->decimal('debt_amount', 15, 2)->default(0); // قەرز
        $table->decimal('discount', 15, 2)->default(0); // داشکاندن
        
        // Currency & Exchange
        $table->foreignId('currency_id')->constrained('currency_configs'); // جۆری پارە
        $table->decimal('exchange_rate', 15, 6)->default(1); // نرخی پارە

        // The "Double-Entry" Links
        $table->foreignId('account_id')->nullable()->constrained('accounts'); // هەژمار (If Debt)
        $table->foreignId('cashbox_id')->nullable()->constrained('cash_boxes'); // قاسە (If Cash)

        // Extras
        $table->text('note')->nullable(); // تێبینی
        $table->string('attachment')->nullable(); // هاوپێچکردنی بەڵگە
        $table->foreignId('created_by')->constrained('users'); // یوزەر

        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
