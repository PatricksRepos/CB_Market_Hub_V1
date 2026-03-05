<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_inquiries', function (Blueprint $table) {
            $table->timestamp('buyer_last_read_at')->nullable()->after('last_message_at');
            $table->timestamp('seller_last_read_at')->nullable()->after('buyer_last_read_at');
            $table->index('buyer_last_read_at');
            $table->index('seller_last_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('listing_inquiries', function (Blueprint $table) {
            $table->dropIndex(['buyer_last_read_at']);
            $table->dropIndex(['seller_last_read_at']);
            $table->dropColumn(['buyer_last_read_at', 'seller_last_read_at']);
        });
    }
};
