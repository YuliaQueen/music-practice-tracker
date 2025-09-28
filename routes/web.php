<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\GoalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $exercises = \App\Domains\Planning\Models\Exercise::forUser(auth()->id())
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
        
    return Inertia::render('Dashboard', [
        'exercises' => $exercises,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Сессии занятий
    Route::resource('sessions', SessionController::class);
    Route::post('/sessions/{session}/start', [SessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/pause', [SessionController::class, 'pause'])->name('sessions.pause');
    Route::post('/sessions/{session}/complete', [SessionController::class, 'complete'])->name('sessions.complete');
    Route::patch('/sessions/{session}/blocks/{block}', [SessionController::class, 'updateBlock'])->name('sessions.blocks.update');
    
    // Статистика
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    
    // Упражнения
    Route::resource('exercises', ExerciseController::class);
    
    // Цели
    Route::resource('goals', GoalController::class);
    Route::patch('/goals/{goal}/progress', [GoalController::class, 'updateProgress'])->name('goals.progress');
    Route::post('/goals/{goal}/complete', [GoalController::class, 'complete'])->name('goals.complete');
    Route::post('/goals/{goal}/toggle', [GoalController::class, 'toggle'])->name('goals.toggle');
});

require __DIR__.'/auth.php';
