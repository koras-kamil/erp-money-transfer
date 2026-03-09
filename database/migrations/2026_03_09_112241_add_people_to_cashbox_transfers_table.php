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
        Schema::table('cashbox_transfers', function (Blueprint $table) {
            $table->string('giver_name')->nullable();
            $table->string('giver_phone')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashbox_transfers', function (Blueprint $table) {
            //
        });
    }
};
