<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Models\Video;
use \App\Models\Module;
use \App\Models\Course;


return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $videos = Video::all();
            $courses = Course::all();
            $videosToUpdate = [];

            foreach ($videos as $video) {
                $videosToUpdate[] = [
                    'video_id' => $video->id,
                    'course_id' => $video->course_id,
                ];
            }

            $table->removeColumn('course_id');
            $table->foreignId('module_id')->constrained('modules');

            foreach ($courses as $course) {
                $module = new Module();
                $module->course_id = $course->id;
                $module->name = 'Módulo 1';
                $module->description = 'Descrição do módulo 1';
                $module->status = 'published';
                $module->save();
            }

            foreach ($videosToUpdate as $video) {
                $video = Video::find($video['video_id']);
                $video->module_id = Module::where('course_id', $video['course_id'])->first()->id;
                $video->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses');
            $table->removeColumn('module_id');
        });
    }
};
