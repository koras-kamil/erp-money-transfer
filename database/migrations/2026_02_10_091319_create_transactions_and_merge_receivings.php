<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        
        // Relationships - Using explicit definitions to avoid "Table not found" errors
        $table->unsignedBigInteger('account_id')->nullable()->index();
        $table->unsignedBigInteger('user_id')->nullable()->index();
        $table->unsignedBigInteger('currency_id')->nullable()->index();
        $table->unsignedBigInteger('cashbox_id')->nullable()->index(); // 🟢 Fixed here

        // Core Data
        $table->string('type', 50)->index(); // 'receive' or 'pay'
        $table->decimal('amount', 20, 2)->default(0);
        $table->decimal('total', 20, 2)->default(0); 
        
        // Details
        $table->decimal('exchange_rate', 20, 6)->default(1);
        $table->decimal('discount', 20, 2)->default(0);
        $table->string('invoice_type')->default('normal');
        
        // Manual Info
        $table->string('statement_id')->nullable();
        $table->timestamp('manual_date')->nullable();
        $table->text('note')->nullable();

        // People
        $table->string('giver_name')->nullable();
        $table->string('giver_mobile')->nullable();
        $table->string('receiver_name')->nullable();
        $table->string('receiver_mobile')->nullable();

        $table->timestamps();
        $table->softDeletes();
    });

    // ... (rest of your migration logi

        // 2. MIGRATE OLD DATA (Move Receivings -> Transactions)
        if (Schema::hasTable('receivings')) {
            $oldRecords = DB::table('receivings')->get();

            foreach ($oldRecords as $record) {
                DB::table('transactions')->insert([
                    'type' => 'receive', // Tag old data as 'receive'
                    'account_id' => $record->account_id,
                    'user_id' => $record->user_id,
                    'currency_id' => $record->currency_id,
                    'cashbox_id' => $record->cashbox_id,
                    'amount' => $record->amount,
                    'total' => ($record->amount - $record->discount),
                    'exchange_rate' => $record->exchange_rate,
                    'discount' => $record->discount,
                    'invoice_type' => $record->invoice_type,
                    'statement_id' => $record->statement_id,
                    'manual_date' => $record->manual_date,
                    'note' => $record->note,
                    'giver_name' => $record->giver_name,
                    'giver_mobile' => $record->giver_mobile,
                    'receiver_name' => $record->receiver_name,
                    'receiver_mobile' => $record->receiver_mobile,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                    'deleted_at' => $record->deleted_at ?? null,
                ]);
            }

            // 3. DROP OLD TABLE
            Schema::drop('receivings');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        // Note: We cannot easily recreate 'receivings' with data in down() without complex logic.
    }
};