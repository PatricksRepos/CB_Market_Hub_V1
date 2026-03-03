<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('reportable_type'); // App\Models\Post, App\Models\Poll
            $table->unsignedBigInteger('reportable_id');

            $table->string('reason'); // spam, scam, hate, harassment, illegal, other
            $table->text('details')->nullable();

            $table->string('status')->default('open')->index(); // open, reviewing, resolved, rejected
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
