<?php

use App\Http\Controllers\Admin\CoursesController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\VideosController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TagCourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:api', 'api'])->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::resource('courses', CoursesController::class, ['except' => ['create', 'edit', 'update']]);
        Route::resource('teachers', App\Http\Controllers\Admin\TeachersController::class, ['except' => ['create', 'edit']]);
        Route::post('courses/{course}/update', [CoursesController::class, 'update'])->name('admin.courses.update');
        Route::post('courses/{course}/disable', [CoursesController::class, 'disable'])->name('admin.courses.disable');
        Route::resource(name: '/courses/{course}/modules', controller: ModulesController::class);
        Route::resource(name: '/courses/{course}/modules/{module}/videos', controller: VideosController::class);
        Route::resource('users', UsersController::class);

        // Tags routes
        Route::resource('tags', TagController::class);
        Route::get('tags/{tag}/courses', [TagController::class, 'courses'])->name('admin.tags.courses');

        // Tag-Course relationship routes
        Route::resource('tag-courses', TagCourseController::class, ['except' => ['create', 'edit', 'update']]);
        Route::delete('tag-courses/tag/{tagId}/course/{courseId}', [TagCourseController::class, 'destroyByTagAndCourse'])->name('admin.tag-courses.destroy-by-tag-and-course');
        Route::get('courses/{course}/tags', [TagCourseController::class, 'getTagsByCourse'])->name('admin.courses.tags');
        Route::get('tags/{tag}/courses', [TagCourseController::class, 'getCoursesByTag'])->name('admin.tags.courses');
    });
});

