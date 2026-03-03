<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 🟢 This index makes SUM() queries lightning fast for the Report Controller!
            $table->index(['cashbox_id', 'currency_id', 'type'], 'cashbox_sum_index');
            
            // Optional: Good for the user statement ledger speed
            $table->index(['account_id', 'target_currency_id'], 'account_balance_index'); 
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('cashbox_sum_index');
            $table->dropIndex('account_balance_index');
        });
    }
};