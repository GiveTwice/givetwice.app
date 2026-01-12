<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('list_invitations', function (Blueprint $table) {
            $table->index('inviter_id');
            $table->index('invitee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_invitations', function (Blueprint $table) {
            $table->dropIndex(['inviter_id']);
            $table->dropIndex(['invitee_id']);
        });
    }
};
