<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profit_groups', function (Blueprint $table) {
            // This adds the 'deleted_at' column needed for SoftDeletes
            if (!Schema::hasColumn('profit_groups', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table('profit_groups', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};