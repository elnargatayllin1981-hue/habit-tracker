<?php

use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\HabitLogController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

/* ---------- Главная ---------- */
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/* ---------- Гость ---------- */
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login',  [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')->name('logout');

/* ---------- Авторизованная зона ---------- */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Привычки
    Route::get   ('/habits/create',       [HabitController::class, 'create'])->name('habits.create');
    Route::post  ('/habits',              [HabitController::class, 'store'])->name('habits.store');
    Route::get   ('/habits/{habit}',      [HabitController::class, 'show'])->name('habits.show');
    Route::get   ('/habits/{habit}/edit', [HabitController::class, 'edit'])->name('habits.edit');
    Route::put   ('/habits/{habit}',      [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}',      [HabitController::class, 'destroy'])->name('habits.destroy');

    // Отчёт по привычке
    Route::get('/habits/{habit}/report',  [ReportController::class, 'show'])->name('habits.report');

    // Отметки
    Route::post  ('/habits/{habit}/logs',           [HabitLogController::class, 'upsert'])->name('habit-logs.upsert');
    Route::delete('/habits/{habit}/logs/{log}',     [HabitLogController::class, 'destroy'])->name('habit-logs.destroy');

    // AI
    Route::post('/habits/{habit}/ai-suggest',  [AiSuggestionController::class, 'generate'])->name('ai.generate');
    Route::get ('/habits/{habit}/ai-history',  [AiSuggestionController::class, 'history'])->name('ai.history');

    // Предложения
    Route::get ('/suggestions',                [SuggestionController::class, 'index'])->name('suggestions.index');
    Route::post('/suggestions/{slug}/adopt',   [SuggestionController::class, 'adopt'])->name('suggestions.adopt');

    // Журнал
    Route::get('/journal',         [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/export',  [JournalController::class, 'export'])->name('journal.export');

    // Тема
    Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
});
