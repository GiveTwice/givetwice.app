<?php

use App\Models\Claim;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->json('reservation')->nullable();
            $table->string('resolution')->nullable();
            $table->dropForeign(['gift_id']);
        });
        Schema::table('claims', fn (Blueprint $table) => $table->unsignedBigInteger('gift_id')->nullable()->change());
        Schema::table('claims', fn (Blueprint $table) => $table->foreign('gift_id')->references('id')->on('gifts')->nullOnDelete());
        Claim::with('gift.lists.creator', 'gift.user')->chunkById(100, function ($claims) {
            foreach ($claims as $claim) {
                $claim->updateQuietly(['reservation' => $claim->snapshot(), 'claimer_email' => $claim->claimer_email ?? $claim->user?->email]);
            }
        });
    }
};
