<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();

            // MANY polls per user (NO unique constraint)
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('question');
            $table->boolean('is_active')->default(true);

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->string('results_visibility', 20)
                ->default('after_end');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
