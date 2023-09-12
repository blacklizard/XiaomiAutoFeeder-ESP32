<?php

use App\Http\Controllers\Web\AnnounceController;
use App\Http\Controllers\Web\FeederController;
use App\Http\Controllers\Web\GroupController;
use App\Http\Controllers\Web\StatisticController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    return redirect()->route('feeders.index');
});

Route::resource('feeders', FeederController::class)->only(['index', 'destroy']);
Route::post('/feeders/{feeder}/name', [FeederController::class, 'name'])->name('feeders.name');
Route::post('/feeders/{feeder}/identify', [FeederController::class, 'identify'])->name('feeders.identify');
Route::post('/feeders/{feeder}/replace_drier', [FeederController::class, 'replaceDrier'])->name('feeders.replace_drier');
Route::post('/feeders/{feeder}/sync', [FeederController::class, 'syncSchedule'])->name('feeders.schedule.sync');

Route::get('/feeders/{feeder}/schedule', [FeederController::class, 'showSchedule'])->name('feeders.schedule.show');
Route::post('/feeders/{feeder}/schedule', [FeederController::class, 'addSchedule'])->name('feeders.schedule.add');
Route::post('/feeders/{feeder}/{schedule}/toggle', [FeederController::class, 'toggleSchedule'])->name('feeders.schedule.toggle');
Route::post('/feeders/{feeder}/{schedule}/remove', [FeederController::class, 'removeSchedule'])->name('feeders.schedule.remove');
Route::post('/feeders/{feeder}/{schedule}/update', [FeederController::class, 'updateSchedule'])->name('feeders.schedule.update');

Route::post('/feeders/{feeder}/dispense/feeder', [FeederController::class, 'dispenseFeeder'])->name('feeders.dispense.feeder');


Route::resource('groups', GroupController::class)->except('show');
Route::post('/groups/{group}/sync', [GroupController::class, 'syncSchedule'])->name('groups.schedule.sync');
Route::get('/groups/{group}/schedule', [GroupController::class, 'showSchedule'])->name('groups.schedule.show');
Route::post('/groups/{group}/schedule', [GroupController::class, 'addSchedule'])->name('groups.schedule.add');
Route::post('/groups/{group}/{schedule}/toggle', [GroupController::class, 'toggleSchedule'])->name('groups.schedule.toggle');
Route::post('/groups/{group}/{schedule}/remove', [GroupController::class, 'removeSchedule'])->name('groups.schedule.remove');
Route::post('/groups/{group}/{schedule}/update', [GroupController::class, 'updateSchedule'])->name('groups.schedule.update');
Route::post('/groups/{group}/dispense/feeder', [GroupController::class, 'dispenseFeeder'])->name('groups.dispense.feeder');


Route::get('/announcements', AnnounceController::class)->name('announcements');
Route::get('/statistics', StatisticController::class)->name('statistics');