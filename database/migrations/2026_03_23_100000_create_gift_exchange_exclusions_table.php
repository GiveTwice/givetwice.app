<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_exchange_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')->constrained('gift_exchanges')->cascadeOnDelete();
            $table->foreignId('giver_id')->constrained('gift_exchange_participants')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('gift_exchange_participants')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exchange_id', 'giver_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_exchange_exclusions');
    }
};
