<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VideoCommentController;
use App\Http\Controllers\VideoRatingController;
use App\Http\Controllers\VideoReportController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\PersonalCourseModulesController;
use App\Http\Controllers\PersonalCourseShareController;
use App\Http\Controllers\PersonalCourseVideosController;
use App\Http\Controllers\PersonalCoursesController;
use Illuminate\Support\Facades\Route;

// class from admin
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CoursesController as AdminCoursesController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->redirectTo('/dashboard');
});

// Invitation routes (sem autenticação)
Route::get('/invite/register/{token}', [App\Http\Controllers\InvitationRegisterController::class, 'show'])->name('invite.register');
Route::post('/invite/register/{token}', [App\Http\Controllers\InvitationRegisterController::class, 'store'])->name('invite.register.store');
Route::get('/invite/accept/{token}', [App\Http\Controllers\InvitationAcceptController::class, 'show'])->name('invite.accept');
Route::post('/invite/accept/{token}', [App\Http\Controllers\InvitationAcceptController::class, 'store'])->name('invite.accept.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/courses/{course}', [CoursesController::class, 'show'])->name('courses.show');

    Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
    Route::get('/teachers', [TeachersController::class, 'index'])->name('teachers.index');

    Route::get('/personal-courses', [PersonalCoursesController::class, 'index'])->name('personal-courses.index');
    Route::post('/personal-courses', [PersonalCoursesController::class, 'store'])->name('personal-courses.store');
    Route::get('/personal-courses/{course}/edit', [PersonalCoursesController::class, 'edit'])->name('personal-courses.edit');
    Route::put('/personal-courses/{course}', [PersonalCoursesController::class, 'update'])->name('personal-courses.update');
    Route::post('/personal-courses/{course}/modules', [PersonalCourseModulesController::class, 'store'])->name('personal-courses.modules.store');
    Route::put('/personal-courses/{course}/modules/{module}', [PersonalCourseModulesController::class, 'update'])->name('personal-courses.modules.update');
    Route::delete('/personal-courses/{course}/modules/{module}', [PersonalCourseModulesController::class, 'destroy'])->name('personal-courses.modules.destroy');
    Route::post('/personal-courses/{course}/modules/{module}/videos', [PersonalCourseVideosController::class, 'store'])->name('personal-courses.videos.store');
    Route::put('/personal-courses/{course}/modules/{module}/videos/{video}', [PersonalCourseVideosController::class, 'update'])->name('personal-courses.videos.update');
    Route::delete('/personal-courses/{course}/modules/{module}/videos/{video}', [PersonalCourseVideosController::class, 'destroy'])->name('personal-courses.videos.destroy');

    Route::get('/personal-courses/share/{token}', [PersonalCourseShareController::class, 'show'])->name('personal-courses.share');
    Route::post('/personal-courses/share/{token}/enroll', [PersonalCourseShareController::class, 'enroll'])->name('personal-courses.share.enroll');

    Route::get('/courses/{course}/watch', [WatchController::class, 'index'])->name('courses.watch');
    Route::get('/courses/{course}/videos/{video}', [WatchController::class, 'show'])->name('courses.videos.show');
    Route::post('/videos/{video}/complete', [WatchController::class, 'complete'])
        ->name('courses.videos.store');

    // Video Comments
    Route::get('/videos/{video}/comments', [VideoCommentController::class, 'index'])->name('videos.comments.index');
    Route::post('/videos/{video}/comments', [VideoCommentController::class, 'store'])->name('videos.comments.store');
    Route::post('/comments/{comment}/reply', [VideoCommentController::class, 'reply'])->name('comments.reply');
    Route::post('/comments/{comment}/like', [VideoCommentController::class, 'toggleLike'])->name('comments.like');

    // Video Ratings
    Route::get('/videos/{video}/rating', [VideoRatingController::class, 'show'])->name('videos.rating.show');
    Route::post('/videos/{video}/rating', [VideoRatingController::class, 'store'])->name('videos.rating.store');

    // Video Reports
    Route::post('/videos/{video}/report', [VideoReportController::class, 'store'])->name('videos.report.store');

    // Community
    Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
    Route::get('/community/posts/{post}', [CommunityController::class, 'show'])->name('community.posts.show');
    Route::post('/community/posts', [CommunityController::class, 'store'])->name('community.posts.store');
    Route::post('/community/posts/{post}/like', [CommunityController::class, 'toggleLike'])->name('community.posts.like');
    Route::post('/community/posts/{post}/comments', [CommunityController::class, 'storeComment'])->name('community.posts.comments.store');

    // Help
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/articles/{article}', [HelpController::class, 'show'])->name('help.articles.show');
    Route::get('/help/categories/{category}', [HelpController::class, 'category'])->name('help.categories.show');

    // Platform switching
    Route::post('/platforms/switch', [App\Http\Controllers\PlatformController::class, 'webSwitch'])->name('platforms.switch');
});

require __DIR__ . '/auth.php';
