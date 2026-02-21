<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->after('last_friend_digest_at');
            $table->timestamp('inactive_warning_sent_at')->nullable()->after('last_active_at');
        });

        DB::statement('UPDATE users SET last_active_at = COALESCE(updated_at, created_at)');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_active_at', 'inactive_warning_sent_at']);
        });
    }
};
