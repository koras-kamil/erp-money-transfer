<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('currency_configs', function (Blueprint $table) {
            // 1. Add the new branch_id column (nullable initially so existing data doesn't break)
            if (!Schema::hasColumn('currency_configs', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
            
            // 2. (Optional) Drop the old text column if you don't need it anymore
            // if (Schema::hasColumn('currency_configs', 'branch')) {
            //     $table->dropColumn('branch');
            // }
        });
    }

    public function down()
    {
        Schema::table('currency_configs', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
            // $table->string('branch')->nullable(); // Restore old column if needed
        });
    }
};