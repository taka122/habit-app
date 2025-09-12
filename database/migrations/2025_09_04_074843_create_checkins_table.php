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
    Schema::create('checkins', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->date('date'); // Asia/Tokyoの“今日”キー
        $table->enum('state', ['planned','done','skipped'])->default('planned');
        $table->string('title', 100);     // 習慣名（自由入力）
        $table->string('genre', 50)->nullable(); // 任意のジャンルタグ
        $table->dateTime('start_at')->nullable();
        $table->dateTime('end_at')->nullable();
        $table->string('reason', 200)->nullable();      // skip用
        $table->string('next_action', 200)->nullable(); // skip用
        $table->unsignedInteger('duration_minutes')->default(0); // 作業時間（分）
        $table->timestamps();

        $table->unique(['user_id','date','title']); // 同日・同タイトルは1件
        $table->index(['user_id','date']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
