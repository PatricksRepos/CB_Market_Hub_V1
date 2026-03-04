<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestion_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 120)->nullable();
            $table->timestamps();

            $table->index(['suggestion_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestion_reports');
    }
};
