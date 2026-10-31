<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_exchange_participants', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->foreignId('wishlist_id')->nullable()->constrained('lists')->nullOnDelete();
            $table->timestamp('whatsapp_opened_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('token_generated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gift_exchange_participants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wishlist_id');
            $table->dropColumn(['whatsapp_opened_at', 'sent_at', 'token_generated_at']);
        });
    }
};
