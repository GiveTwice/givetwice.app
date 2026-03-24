<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NEEDS_DEPLOY_REVIEW
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE gifts MODIFY COLUMN fetch_status ENUM('pending','fetching','completed','failed','skipped') DEFAULT 'pending'");
            DB::statement('ALTER TABLE gifts MODIFY COLUMN url VARCHAR(2048) NULL');
        } else {
            // SQLite: enum is stored as varchar, just make url nullable via column recreation
            Schema::table('gifts', function (Blueprint $table) {
                $table->string('url', 2048)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE gifts SET fetch_status = 'failed' WHERE fetch_status = 'skipped'");
            DB::statement("UPDATE gifts SET url = '' WHERE url IS NULL");
            DB::statement("ALTER TABLE gifts MODIFY COLUMN fetch_status ENUM('pending','fetching','completed','failed') DEFAULT 'pending'");
            DB::statement('ALTER TABLE gifts MODIFY COLUMN url VARCHAR(2048) NOT NULL');
        } else {
            DB::table('gifts')->where('fetch_status', 'skipped')->update(['fetch_status' => 'failed']);
            DB::table('gifts')->whereNull('url')->update(['url' => '']);
            Schema::table('gifts', function (Blueprint $table) {
                $table->string('url', 2048)->nullable(false)->change();
            });
        }
    }
};
