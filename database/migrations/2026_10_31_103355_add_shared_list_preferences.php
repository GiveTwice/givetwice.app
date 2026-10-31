<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table): void {
            $table->string('occasion')->nullable();
            $table->date('occasion_date')->nullable();
            $table->boolean('spoiler_mode')->default(false);
            $table->timestamp('spoiler_confirmed_at')->nullable();
        });
        Schema::table('gifts', function (Blueprint $table): void {
            $table->boolean('most_wanted')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table): void {
            $table->dropColumn(['occasion', 'occasion_date', 'spoiler_mode', 'spoiler_confirmed_at']);
        });
        Schema::table('gifts', function (Blueprint $table): void {
            $table->dropColumn('most_wanted');
        });
    }
};
