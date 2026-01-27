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
        // Check if the column exists FIRST
        if (!Schema::hasColumn('currency_configs', 'math_operator')) {
            Schema::table('currency_configs', function (Blueprint $table) {
                $table->string('math_operator', 1)->default('/')->after('price_single'); 
            });
        }
    }

public function down()
{
    
}
};
