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
    Schema::create('daily_reports', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->date('date');                          // 日付キー
        $table->tinyInteger('mood')->nullable();       // 1..5（任意）
        $table->tinyInteger('effort')->nullable();     // 1..5（任意）
        $table->text('content');                       // 本文
        $table->timestamps();

        $table->unique(['user_id','date'], 'dr_user_date_unique'); // 1ユーザー1日1件
        $table->index(['user_id','date']);
    });
}

public function down(): void
{
    Schema::dropIfExists('daily_reports');
}
};
