<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('friend_notifications_enabled')->default(true)->after('is_admin');
            $table->timestamp('last_friend_digest_at')->nullable()->after('friend_notifications_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['friend_notifications_enabled', 'last_friend_digest_at']);
        });
    }
};
