<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profit_types', function (Blueprint $table) {
            // Add 'code' if it doesn't exist
            if (!Schema::hasColumn('profit_types', 'code')) {
                $table->string('code')->nullable()->after('id'); 
            }
            
            // Add 'branch_id' if it doesn't exist
            if (!Schema::hasColumn('profit_types', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            }

            // Add 'created_by' if it doesn't exist
            if (!Schema::hasColumn('profit_types', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            // Add 'deleted_by' if it doesn't exist
            if (!Schema::hasColumn('profit_types', 'deleted_by')) {
                 $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('profit_types', function (Blueprint $table) {
            $table->dropColumn(['code', 'branch_id', 'created_by', 'deleted_by']);
        });
    }
};