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
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();   // e.g., 'base_currency_id'
        $table->text('value')->nullable(); // e.g., '1'
        $table->timestamps();
    });

    // Insert Default Base Currency (Assuming ID 1 is USD)
    DB::table('settings')->insert([
        'key' => 'base_currency_id',
        'value' => '1',
        'created_at' => now(), 
        'updated_at' => now()
    ]);
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
