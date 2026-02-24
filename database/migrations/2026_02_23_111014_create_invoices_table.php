<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // 会員
            $table->foreignId('member_id')
                ->constrained()
                ->cascadeOnDelete();

            // 請求書番号
            $table->string('invoice_no')->unique();

            // 日付系
            $table->date('issued_at')->nullable();   // 発行日
            $table->date('due_date')->nullable();    // 支払期限
            $table->date('paid_at')->nullable();     // 入金日

            // 金額
            $table->integer('amount')->default(0);

            // ステータス
            $table->string('status')->default('unpaid');
            // unpaid / paid / canceled

            // 備考
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
