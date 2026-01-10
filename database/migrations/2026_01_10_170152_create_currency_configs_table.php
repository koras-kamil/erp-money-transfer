<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('currency_configs', function (Blueprint $table) {
            $table->id(); // Auto Increment
            
            $table->string('currency_type'); // e.g., 'USD', 'IQD'
            $table->string('symbol')->nullable(); // e.g., '$'
            $table->integer('digit_number')->default(0); // e.g., 0, 2, 3
            
            // PRICES: Using decimal(18, 3) to support 3 decimal places safely
            $table->decimal('price_total', 18, 3)->default(0);
            $table->decimal('price_single', 18, 3)->default(0);
            $table->decimal('price_sell', 18, 3)->default(0);
            
            $table->string('branch')->nullable(); // Can be changed to integer if using Branch IDs
            $table->boolean('is_active')->default(true); // True = Active, False = No
            
            $table->timestamps(); // Created_at, Updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('currency_configs');
    }
};