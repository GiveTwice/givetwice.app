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
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url', 2048);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_in_cents')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('image_url', 2048)->nullable();
            $table->enum('fetch_status', ['pending', 'fetching', 'completed', 'failed'])->default('pending');
            $table->timestamp('fetched_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('fetch_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
