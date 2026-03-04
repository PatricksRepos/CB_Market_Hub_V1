<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('body')->nullable();
            $table->integer('price_cents')->nullable();
            $table->string('location')->nullable();
            $table->string('category')->default('general'); // general,buy,sell,trade,services
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->index(['is_active','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
