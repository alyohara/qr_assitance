<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectController;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Subject;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'subjectsCount' => Subject::query()->count(),
        'sessionsCount' => ClassSession::query()->count(),
        'attendancesCount' => Attendance::query()->count(),
        'activeSessions' => ClassSession::query()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with('subject')
            ->latest('starts_at')
            ->get(),
    ]);
})->middleware(['auth', 'verified', 'professor'])->name('dashboard');

Route::get('/asistencia/{classSession}', [AttendanceController::class, 'create'])->name('attendance.scan');
Route::post('/asistencia/{classSession}', [AttendanceController::class, 'store'])
    ->middleware('auth')
    ->name('attendance.store');

Route::middleware('signed')->group(function () {
    Route::get('/public/sessions/{classSession}/{mode}', [SessionController::class, 'publicBoard'])
        ->whereIn('mode', ['qr', 'qr-token'])
        ->name('sessions.public-board');
    Route::get('/public/sessions/{classSession}/qr-payload', [SessionController::class, 'publicQrPayload'])
        ->name('sessions.public-qr-payload');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'professor'])->group(function () {

    Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('sessions', SessionController::class)
        ->parameters(['sessions' => 'classSession'])
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/sessions/{classSession}/qr-payload', [SessionController::class, 'qrPayload'])->name('sessions.qr-payload');
    Route::get('/sessions/{classSession}/export-csv', [SessionController::class, 'exportCsv'])->name('sessions.export-csv');
    Route::get('/subjects/{subject}/export-csv', [SubjectController::class, 'exportCsv'])->name('subjects.export-csv');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';
