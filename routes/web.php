<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\DashboardController;
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
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
    Route::get('/courses', [AdminCoursesController::class, 'index']);
    Route::get('/', [AdminDashboardController::class, 'index']);
});

require __DIR__ . '/auth.php';
