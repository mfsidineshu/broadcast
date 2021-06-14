<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\BroadcastViewersController;

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
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::get('login', [AuthController::class, 'index'])->name('login');
Route::post('post-login', [AuthController::class, 'postLogin'])->name('login.post');
Route::get('registration', [AuthController::class, 'registration'])->name('register');
Route::post('post-registration', [AuthController::class, 'postRegistration'])->name('register.post');

Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('dashboard', [AuthController::class, 'dashboard'])->middleware('auth')->name('dashboard');
Route::get('start-broadcast', [BroadcastController::class, 'broadcastPage'])->middleware('auth');
Route::post('save-chunks', [BroadcastController::class, 'saveStreamToAFile'])->middleware('auth');

Route::get('watch-broadcast/{folder}', [BroadcastController::class, 'watchBroadcastPage'])->middleware('auth');
Route::get('stream-broadcast/{folder}/{bitRate}.m3u8', [BroadcastController::class, 'fetchPlayLists'])->middleware('auth');
Route::get('stream-broadcast/{folder}/{fragment}.ts', [BroadcastController::class, 'fetchSegments'])->middleware('auth');

Route::post('viewing-status', [BroadcastViewersController::class, 'addOrUpdateBroadViewingStatus'])->middleware('auth');
Route::get('broadcast-viewers', [ BroadcastViewersController::class, 'broadcastViewers'])->middleware('auth');

Route::delete('delete-broadcast/{folder}', [BroadcastController::class, 'endBroadcast'])->middleware('auth');
