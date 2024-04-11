<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\WatchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Course;

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

Route::get('/dashboard', function () {
    $courses = Course::all();

    return Inertia::render('Dashboard')
        ->with('courses', $courses);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/courses/{course}', [CoursesController::class, 'show'])->name('courses.show');

    Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
    Route::get('/teachers', [TeachersController::class, 'index'])->name('teachers.index');

    Route::get('/courses/{course}/watch', [WatchController::class, 'index'])->name('courses.watch');
    Route::get('/courses/{course}/videos/{video}', [WatchController::class, 'show'])->name('courses.videos.show');
});

require __DIR__ . '/auth.php';
