<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followed_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->boolean('notifications')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'list_id']);
            $table->index('list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followed_lists');
    }
};
