<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('watch_videos', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['watching', 'finished']);
            $table->foreignId('user_id')->constrained();
            $table->foreignId('video_id')->constrained();
            $table->unique(['user_id', 'video_id']);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_videos');
    }
};
