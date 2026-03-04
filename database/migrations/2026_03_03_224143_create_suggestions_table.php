<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title', 140);
            $table->text('body')->nullable();

            $table->string('status', 20)->default('open'); // open, planned, in_progress, done, rejected
            $table->boolean('is_anonymous')->default(false);

            $table->timestamps();

            $table->index(['status','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
