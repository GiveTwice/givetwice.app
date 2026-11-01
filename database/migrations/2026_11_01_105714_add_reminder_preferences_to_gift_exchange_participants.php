<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_exchange_participants', function (Blueprint $table) {
            $table->timestamp('reveal_reminder_sent_at')->nullable();
            $table->timestamp('wishlist_nudge_sent_at')->nullable();
            $table->timestamp('reminders_muted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gift_exchange_participants', function (Blueprint $table) {
            $table->dropColumn(['reveal_reminder_sent_at', 'wishlist_nudge_sent_at', 'reminders_muted_at']);
        });
    }
};
