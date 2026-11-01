<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('followed_lists', function (Blueprint $table) {
            $table->boolean('manager_updates')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('followed_lists', function (Blueprint $table) {
            $table->dropColumn('manager_updates');
        });
    }
};
