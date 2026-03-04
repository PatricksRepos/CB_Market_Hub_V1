<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(
                ['type', 'is_hidden', 'marketplace_action', 'created_at'],
                'posts_marketplace_feed_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_marketplace_feed_idx');
        });
    }
};
