<?php

use App\Models\Course;
use App\Models\Module;
use App\Models\Video;
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
        Schema::table('video', function (Blueprint $table) {
            $courses = Course::all();

            foreach ($courses as $course) {
                $module = new Module();
                $module->course_id = $course->id;
                $module->name = 'Módulo 1';
                $module->description = 'Descrição do módulo 1';
                $module->status = 'published';
                $module->save();
            }

            Video::all()->each(function ($video) {
                if(isset($video->id)) {
                    $module = $video->module()->first();
                    $course = $module->course()->first();
                    $video->course_id = $course->id;
                    $video->save();
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video', function (Blueprint $table) {
            //
        });
    }
};
