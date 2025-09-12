<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('checkins', 'duration_minutes')) {
            Schema::table('checkins', function (Blueprint $table) {
                $table->unsignedInteger('duration_minutes')->default(0)->after('next_action');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('checkins', 'duration_minutes')) {
            Schema::table('checkins', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }
};

