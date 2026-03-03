<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // marketplace, business, discussion
            $table->string('type')->index();

            $table->string('title');
            $table->text('body');

            // Marketplace fields
            $table->string('marketplace_action')->nullable()->index(); // buy, sell, trade
            $table->decimal('price', 10, 2)->nullable()->index();
            $table->string('location')->nullable()->index();
            $table->string('condition')->nullable();

            // Anonymous display (account required)
            $table->boolean('is_anonymous')->default(false);
            $table->string('anonymous_name')->nullable();

            // Moderation
            $table->boolean('is_hidden')->default(false)->index();
            $table->timestamp('hidden_at')->nullable();
            $table->string('hidden_reason')->nullable();

            // Status lifecycle
            $table->string('status')->default('active')->index(); // active, sold, archived

            // Promoted (free now, paid later)
            $table->boolean('is_promoted')->default(false)->index();
            $table->timestamp('promoted_until')->nullable()->index();

            $table->timestamps();
            $table->index(['category_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
