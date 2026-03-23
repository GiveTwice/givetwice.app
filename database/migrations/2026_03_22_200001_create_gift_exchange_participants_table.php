<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_exchange_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')->constrained('gift_exchanges')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->foreignId('assigned_to_participant_id')->nullable()->constrained('gift_exchange_participants')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['exchange_id', 'email']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_exchange_participants');
    }
};
