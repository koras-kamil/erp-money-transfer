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
    Schema::table('profit_groups', function (Blueprint $table) {
        if (!Schema::hasColumn('profit_groups', 'created_by')) {
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
        }
    });
}

public function down()
{
    Schema::table('profit_groups', function (Blueprint $table) {
        $table->dropColumn('created_by');
    });
}
};
