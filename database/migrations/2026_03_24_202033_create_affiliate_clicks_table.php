<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('list_id')->nullable()->constrained('lists')->nullOnDelete();
            $table->string('url', 2048);
            $table->text('affiliate_url');
            $table->string('retailer_domain', 255);
            $table->string('ip_hash', 64);
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();

            $table->index('gift_id');
            $table->index('list_id');
            $table->index('retailer_domain');
            $table->index('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
