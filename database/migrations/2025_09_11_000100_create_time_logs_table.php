<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('time_logs')) {
            Schema::create('time_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('checkin_id')->nullable();
                $table->dateTime('started_at');
                $table->dateTime('ended_at')->nullable();
                $table->unsignedInteger('duration_sec')->default(0);
                $table->timestamps();

                $table->index(['user_id', 'ended_at']);
                $table->index(['user_id', 'started_at']);
                // Foreign keys are optional in some setups; add if desired
                // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                // $table->foreign('checkin_id')->references('id')->on('checkins')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};

