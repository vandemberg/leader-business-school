<?php

use App\Http\Controllers\Admin\CoursesController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\VideosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

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
        Route::post('courses/{course}/update', [CoursesController::class, 'update'])->name('courses.update');
        Route::resource(name: '/courses/{course}/modules', controller: ModulesController::class);
        Route::resource(name: '/courses/{course}/modules/{module}/videos', controller: VideosController::class);
    });
});

