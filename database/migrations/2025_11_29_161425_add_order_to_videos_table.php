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
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('order')->nullable()->after('module_id');
        });

        // Popular order baseado na ordem atual (id) dentro de cada módulo
        $modules = \App\Models\Module::with('videos')->get();
        
        foreach ($modules as $module) {
            $videos = $module->videos()->orderBy('id')->get();
            $order = 1;
            
            foreach ($videos as $video) {
                $video->order = $order;
                $video->save();
                $order++;
            }
        }

        // Tornar order não-nullable após popular
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('order')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
