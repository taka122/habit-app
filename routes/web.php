<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\TimeLogController;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated area
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Today)
    Route::get('/dashboard', [CheckinController::class, 'index'])->name('dashboard');

    // Practice page (optional)
    Route::get('/practice', [CheckinController::class, 'index2'])->name('practice.index');

    // Calendar view
    Route::get('/calendar', function () {
        return view('calendar.index');
    })->name('calendar.index');

    // Today declarations (planned)
    Route::get('/plans/create', [CheckinController::class, 'create'])->name('plans.create');
    Route::post('/plans', [CheckinController::class, 'store'])->name('plans.store');

    // Status updates
    Route::post('/checkins/{checkin}/done', [CheckinController::class, 'done'])->name('checkins.done');
    Route::get('/checkins/{checkin}/skip', [CheckinController::class, 'skipForm'])->name('checkins.skip.form');
    Route::post('/checkins/{checkin}/skip', [CheckinController::class, 'skip'])->name('checkins.skip');

    // FullCalendar feeds
    Route::get('/checkins/events', [CheckinController::class, 'events'])->name('checkins.events');

    // Edit/Delete
    Route::get('/checkins/{checkin}/edit', [CheckinController::class, 'edit'])->name('checkins.edit');
    Route::patch('/checkins/{checkin}', [CheckinController::class, 'update'])->name('checkins.update');
    Route::delete('/checkins/{checkin}', [CheckinController::class, 'destroy'])->name('checkins.destroy');

    // Save measured time (add minutes)
    Route::post('/checkins/duration', [CheckinController::class, 'addDuration'])->name('checkins.addDuration');

    // TimeLog API + list
    Route::post('/timelog/start', [TimeLogController::class, 'start'])->name('timelog.start');
    Route::post('/timelog/stop',  [TimeLogController::class, 'stop'])->name('timelog.stop');
    Route::get('/timelog/events', [TimeLogController::class, 'events'])->name('timelog.events');
    Route::get('/timelogs',       [TimeLogController::class, 'index'])->name('timelog.index');

    // Daily reports
    Route::get('/reports',                [DailyReportController::class, 'index'])->name('reports.index');
    Route::post('/reports',               [DailyReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}/edit',  [DailyReportController::class, 'edit'])->name('reports.edit');
    Route::patch('/reports/{report}',     [DailyReportController::class, 'update'])->name('reports.update');
    Route::get('/reports/show',           [DailyReportController::class, 'show'])->name('reports.show');

    // History
    Route::get('/history', [\App\Http\Controllers\HistoryController::class, 'index'])->name('history.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
