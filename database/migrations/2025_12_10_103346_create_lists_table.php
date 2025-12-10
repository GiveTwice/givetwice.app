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
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->enum('filter_type', ['all', 'criteria', 'manual'])->default('manual');
            $table->json('filter_criteria')->nullable();
            $table->string('cover_image', 2048)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('slug');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lists');
    }
};
