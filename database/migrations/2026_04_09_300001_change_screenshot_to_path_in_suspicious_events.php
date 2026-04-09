<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suspicious_events', function (Blueprint $table) {
            $table->dropColumn('screenshot');
        });

        Schema::table('suspicious_events', function (Blueprint $table) {
            $table->string('screenshot_path')->nullable()->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('suspicious_events', function (Blueprint $table) {
            $table->dropColumn('screenshot_path');
        });

        Schema::table('suspicious_events', function (Blueprint $table) {
            $table->longText('screenshot')->nullable()->after('points');
        });
    }
};
