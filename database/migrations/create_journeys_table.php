<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('path_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['started', 'paused', 'completed', 'abandoned'])->default('started');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->decimal('distance_traveled', 10, 2)->nullable(); // actual distance in km
            $table->integer('actual_duration')->nullable(); // in minutes
            $table->integer('visited_checkpoints')->default(0);
            $table->json('recorded_positions')->nullable(); // GPS tracking data
            $table->json('weather_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['path_id', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};
