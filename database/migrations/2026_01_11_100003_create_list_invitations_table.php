<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('token', 64)->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['list_id', 'email']);
            $table->index(['email', 'accepted_at', 'declined_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_invitations');
    }
};
