<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('currency_configs', function (Blueprint $table) {
            // Add 'created_by' as a nullable foreign key linking to 'users' table
            if (!Schema::hasColumn('currency_configs', 'created_by')) {
                $table->foreignId('created_by')
                      ->nullable()
                      ->after('is_active') // Places it after 'is_active' column
                      ->constrained('users') // Links to 'id' on 'users' table
                      ->nullOnDelete(); // If user is deleted, set this to NULL
            }
        });
    }

    public function down()
    {
        Schema::table('currency_configs', function (Blueprint $table) {
            // Drop foreign key first, then the column
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};