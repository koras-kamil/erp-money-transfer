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
    Schema::table('currency_configs', function (Blueprint $table) {
        // Default is divide (/) because most currencies (IQD, AED, etc.) work that way against USD
        $table->string('math_operator', 5)->default('/')->after('symbol'); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_configs', function (Blueprint $table) {
            //
        });
    }
};
