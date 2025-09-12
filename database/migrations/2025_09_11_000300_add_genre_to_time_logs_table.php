<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('time_logs', 'genre')) {
                $table->string('genre', 50)->nullable()->after('checkin_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            if (Schema::hasColumn('time_logs', 'genre')) {
                $table->dropColumn('genre');
            }
        });
    }
};

