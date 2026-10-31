<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('gift_id')->index();
            $table->unsignedBigInteger('list_id')->nullable();
            $table->string('email')->index();
            $table->string('locale', 2);
            $table->json('before');
            $table->json('after');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('muted_at')->nullable();
            $table->timestamps();
        });
    }
};
