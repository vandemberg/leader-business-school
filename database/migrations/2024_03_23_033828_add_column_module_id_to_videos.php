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
        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::drop('module_videos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('module_id');
        });

        if (!Schema::hasTable('module_videos')) {
            Schema::create('module_videos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained()->onDelete('cascade');
                $table->foreignId('video_id')->constrained()->onDelete('cascade');
                $table->integer('position')->default(0);
                $table->timestamps();
            });
        }
    }
};
