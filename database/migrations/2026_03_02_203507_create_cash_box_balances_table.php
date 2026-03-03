<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_box_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_box_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currency_configs')->cascadeOnDelete();
            $table->decimal('balance', 20, 2)->default(0); // Large decimal for Toman/Dinar
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_box_balances');
    }
};