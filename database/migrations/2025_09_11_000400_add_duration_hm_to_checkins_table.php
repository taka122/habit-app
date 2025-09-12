<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            if (!Schema::hasColumn('checkins', 'duration_hm')) {
                $table->string('duration_hm', 5)->default('00:00')->after('duration_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            if (Schema::hasColumn('checkins', 'duration_hm')) {
                $table->dropColumn('duration_hm');
            }
        });
    }
};

