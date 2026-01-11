<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('lists')
            ->orderBy('id')
            ->each(function ($list) use ($now) {
                DB::table('list_user')->insert([
                    'list_id' => $list->id,
                    'user_id' => $list->creator_id,
                    'joined_at' => $list->created_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('list_user')->truncate();
    }
};
