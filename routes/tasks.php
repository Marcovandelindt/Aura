<?php

use App\Http\Controllers\Tasks\TaakController;
use Illuminate\Support\Facades\Route;

Route::get('/taken', [TaakController::class, 'index'])->name('tasks.index');
Route::post('/taken', [TaakController::class, 'store'])->name('tasks.store');
Route::post('/taken/{task}/voltooien', [TaakController::class, 'complete'])->name('tasks.complete');
Route::post('/taken/{task}/ongedaan', [TaakController::class, 'uncomplete'])->name('tasks.uncomplete');
Route::delete('/taken/{task}', [TaakController::class, 'destroy'])->name('tasks.destroy');
Route::patch('/taken/{task}/deadline', [TaakController::class, 'updateDueDate'])->name('tasks.update-due-date');
Route::post('/taken/voortgang/reset', [TaakController::class, 'resetProgress'])->name('tasks.reset-progress');
