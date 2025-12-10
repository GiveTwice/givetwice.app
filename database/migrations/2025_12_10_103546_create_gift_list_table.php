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
        Schema::create('gift_list', function (Blueprint $table) {
            $table->foreignId('gift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('list_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->primary(['gift_id', 'list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_list');
    }
};
