<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // nullableのまま長さだけ変更
            $table->string('number', 5)->nullable()->change();

            // unique制約追加
            $table->unique('number');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['number']);

            // 元に戻す
            $table->string('number', 20)->nullable()->change();
        });
    }
};