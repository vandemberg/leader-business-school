<?php

use App\Http\Controllers\Admin\CoursesController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\VideosController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TagCourseController;
use App\Http\Controllers\Admin\HelpArticlesController;
use App\Http\Controllers\Admin\HelpCategoriesController;
use App\Http\Controllers\Admin\DashboardStatsController;
use App\Http\Controllers\Admin\BadgesController;
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
        Route::post('courses/{course}/modules/reorder', [ModulesController::class, 'reorder'])->name('admin.courses.modules.reorder');
        Route::resource(name: '/courses/{course}/modules/{module}/videos', controller: VideosController::class);
        Route::post('courses/{course}/videos/reorder', [VideosController::class, 'reorder'])->name('admin.courses.videos.reorder');

        // Users routes - apenas index, show, destroy (sem store/update)
        Route::get('users', [UsersController::class, 'index'])->name('admin.users.index');
        Route::get('users/{user}', [UsersController::class, 'show'])->name('admin.users.show');
        Route::delete('users/{user}', [UsersController::class, 'removeFromPlatform'])->name('admin.users.destroy');
        Route::post('users/invite', [UsersController::class, 'invite'])->name('admin.users.invite');
        Route::delete('users/{user}/platform', [UsersController::class, 'removeFromPlatform'])->name('admin.users.remove-from-platform');

        // Tags routes
        Route::resource('tags', TagController::class);
        Route::get('tags/{tag}/courses', [TagController::class, 'courses'])->name('admin.tags.courses');

        // Tag-Course relationship routes
        Route::resource('tag-courses', TagCourseController::class, ['except' => ['create', 'edit', 'update']]);
        Route::delete('tag-courses/tag/{tagId}/course/{courseId}', [TagCourseController::class, 'destroyByTagAndCourse'])->name('admin.tag-courses.destroy-by-tag-and-course');
        Route::get('courses/{course}/tags', [TagCourseController::class, 'getTagsByCourse'])->name('admin.courses.tags');
        Route::get('tags/{tag}/courses', [TagCourseController::class, 'getCoursesByTag'])->name('admin.tags.courses');

        // Platform switching routes
        Route::get('platforms', [App\Http\Controllers\PlatformController::class, 'index'])->name('admin.platforms.index');
        Route::post('platforms/switch', [App\Http\Controllers\PlatformController::class, 'switch'])->name('admin.platforms.switch');

        // Help Articles (FAQ) routes
        Route::resource('help-articles', HelpArticlesController::class);

        // Help Categories routes
        Route::resource('help-categories', HelpCategoriesController::class);

        // Badges routes
        Route::resource('badges', BadgesController::class);

        // Dashboard analytics
        Route::get('dashboard', [DashboardStatsController::class, 'index'])->name('admin.dashboard.stats');
    });

    // Invitation routes (sem autenticação para permitir aceitar convites)
    Route::post('invitations/{token}/accept', [App\Http\Controllers\Admin\InvitationController::class, 'accept'])->name('admin.invitations.accept');
});

