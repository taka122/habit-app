<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('checkins', 'genre')) {
            Schema::table('checkins', function (Blueprint $table) {
                $table->string('genre', 50)->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('checkins', 'genre')) {
            Schema::table('checkins', function (Blueprint $table) {
                $table->dropColumn('genre');
            });
        }
    }
};

