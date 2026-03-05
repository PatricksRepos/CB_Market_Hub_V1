<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['listing_id', 'buyer_user_id']);
            $table->index(['seller_user_id', 'last_message_at']);
            $table->index(['buyer_user_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_inquiries');
    }
};
