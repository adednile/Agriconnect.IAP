<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safely add the order_id column if it doesn't exist
        if (!Schema::hasColumn('bids', 'order_id')) {
            Schema::table('bids', function (Blueprint $table) {
                $table->foreignId('order_id')->nullable()->after('id');
                $table->index('order_id');
            });
            
            \Illuminate\Support\Facades\Log::info('✅ Added missing order_id column to bids table');
        }
    }

    public function down(): void
    {
        // We won't remove the column in rollback to avoid data loss
        // This is a safe migration that only adds, doesn't remove
    }
};