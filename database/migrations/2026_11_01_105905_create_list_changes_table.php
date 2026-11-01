<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('gift_id');
            $table->string('kind');
            $table->string('title')->nullable();
            $table->timestamps();
            $table->index(['list_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_changes');
    }
};
