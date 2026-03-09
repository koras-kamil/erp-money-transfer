<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cashbox_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_cashbox_id')->constrained('cash_boxes');
            $table->foreignId('to_cashbox_id')->constrained('cash_boxes');
            $table->foreignId('from_currency_id')->constrained('currency_configs');
            $table->foreignId('to_currency_id')->constrained('currency_configs');
            
            $table->decimal('amount_sent', 15, 2);
            $table->decimal('amount_received', 15, 2);
            $table->decimal('exchange_rate', 15, 6)->nullable();
            
            $table->dateTime('manual_date');
            $table->string('statement_id')->nullable();
            $table->text('note')->nullable();
            
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cashbox_transfers');
    }
};