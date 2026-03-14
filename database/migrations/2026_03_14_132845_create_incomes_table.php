<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->string('manual_voucher')->nullable();
            $table->date('income_date');
            
            // 🟢 گریمانە دەکەین خشتەی جۆرەکانی قازانج ناوی type_profits ە
            $table->unsignedBigInteger('profit_category_id'); 
            
            $table->decimal('cash_amount', 20, 2)->default(0);
            $table->decimal('debt_amount', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            
            $table->unsignedBigInteger('currency_id');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('cashbox_id')->nullable();
            
            $table->text('note')->nullable();
            $table->string('attachment')->nullable();
            $table->unsignedBigInteger('created_by');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('incomes');
    }
};