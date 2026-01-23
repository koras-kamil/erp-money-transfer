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
    Schema::table('profit_types', function (Blueprint $table) {
        if (!Schema::hasColumn('profit_types', 'deleted_at')) {
            $table->softDeletes()->after('updated_at');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_types', function (Blueprint $table) {
            //
        });
    }
};
