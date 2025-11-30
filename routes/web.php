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
