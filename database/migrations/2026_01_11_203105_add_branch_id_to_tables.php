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
    // 1. Link Users to Branches (Check first)
    if (!Schema::hasColumn('users', 'branch_id')) {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
        });
    }

    // 2. Link Cash Boxes to Branches (Check first - THIS WAS YOUR ERROR)
    if (!Schema::hasColumn('cash_boxes', 'branch_id')) {
        Schema::table('cash_boxes', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
        });
    }

    // 3. Link Currencies to Branches (Check first)
    if (!Schema::hasColumn('currency_configs', 'branch_id')) {
        Schema::table('currency_configs', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    // This allows you to undo the change if needed
    Schema::table('users', function (Blueprint $table) { $table->dropForeign(['branch_id']); $table->dropColumn('branch_id'); });
    Schema::table('cash_boxes', function (Blueprint $table) { $table->dropForeign(['branch_id']); $table->dropColumn('branch_id'); });
    Schema::table('currency_configs', function (Blueprint $table) { $table->dropForeign(['branch_id']); $table->dropColumn('branch_id'); });
}
};
