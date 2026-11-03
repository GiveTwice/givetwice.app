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
        Schema::table('claims', function (Blueprint $table) {
            $table->timestamp('receipt_requested_at')->nullable();
            $table->timestamp('receipt_sent_at')->nullable();
            $table->string('receipt_locale', 5)->nullable();
            $table->index(['claimer_email', 'receipt_sent_at', 'receipt_requested_at'], 'claims_pending_receipts_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex('claims_pending_receipts_index');
            $table->dropColumn(['receipt_requested_at', 'receipt_sent_at', 'receipt_locale']);
        });
    }
};
