<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('checkins', 'end_at')) {
            Schema::table('checkins', function (Blueprint $table) {
                $table->dateTime('end_at')->nullable()->after('start_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('checkins', 'end_at')) {
            Schema::table('checkins', function (Blueprint $table) {
                $table->dropColumn('end_at');
            });
        }
    }
};

