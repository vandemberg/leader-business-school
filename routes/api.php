<?php

use App\Http\Controllers\Admin\CoursesController;
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

Route::post('admin/login', [AuthController::class, 'login']);
Route::post('admin/refresh', [AuthController::class, 'refresh'])->middleware('api');
Route::resource('admin/courses', CoursesController::class);
