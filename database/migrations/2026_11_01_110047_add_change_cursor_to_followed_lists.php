<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('followed_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('last_change_id')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('followed_lists', function (Blueprint $table) {
            $table->dropColumn('last_change_id');
        });
    }
};
