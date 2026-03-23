<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_exchanges', function (Blueprint $table) {
            $table->string('join_token', 32)->unique()->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('gift_exchanges', function (Blueprint $table) {
            $table->dropColumn('join_token');
        });
    }
};
