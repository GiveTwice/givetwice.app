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
        Schema::table('lists', function (Blueprint $table) {
            $table->dropColumn('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->string('cover_image', 2048)->nullable();
        });
    }
};
