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
        Schema::table('gifts', function (Blueprint $table) {
            $table->json('fetch_error')->nullable()->after('fetch_status');
            $table->unsignedTinyInteger('fetch_attempts')->default(0)->after('fetch_error');
        });
    }

    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn(['fetch_error', 'fetch_attempts']);
        });
    }
};
