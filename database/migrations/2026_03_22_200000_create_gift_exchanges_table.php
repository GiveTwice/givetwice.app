<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('budget_amount')->nullable();
            $table->string('budget_currency', 3)->default('EUR');
            $table->date('event_date');
            $table->string('slug')->unique();
            $table->string('join_token', 32)->unique()->nullable();
            $table->enum('status', ['draft', 'drawn'])->default('draft');
            $table->timestamp('draw_completed_at')->nullable();
            $table->string('locale', 5)->default('en');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_exchanges');
    }
};
