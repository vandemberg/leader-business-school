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
        Schema::table('modules', function (Blueprint $table) {
            $table->integer('order')->nullable()->after('course_id');
        });

        // Popular order baseado na ordem atual (id) dentro de cada curso
        $courses = \App\Models\Course::with('modules')->get();

        foreach ($courses as $course) {
            $modules = $course->modules()->orderBy('id')->get();
            $order = 1;

            foreach ($modules as $module) {
                $module->order = $order;
                $module->save();
                $order++;
            }
        }

        // Tornar order não-nullable após popular
        Schema::table('modules', function (Blueprint $table) {
            $table->integer('order')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
