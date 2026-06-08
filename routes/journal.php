<?php

use App\Http\Controllers\Journal\JournalController;
use App\Http\Controllers\Journal\JournalStatsController;
use App\Http\Controllers\Journal\JournalTagController;
use Illuminate\Support\Facades\Route;

Route::prefix('journal')->group(function () {
    Route::get('/', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/stats', [JournalStatsController::class, 'index'])->name('journal.stats');
    Route::get('/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/{journalEntry}', [JournalController::class, 'show'])->name('journal.show');
    Route::get('/{journalEntry}/edit', [JournalController::class, 'edit'])->name('journal.edit');
    Route::put('/{journalEntry}', [JournalController::class, 'update'])->name('journal.update');
    Route::delete('/{journalEntry}', [JournalController::class, 'destroy'])->name('journal.destroy');
});

Route::prefix('journal-tags')->group(function () {
    Route::post('/', [JournalTagController::class, 'store'])->name('journal.tags.store');
    Route::put('/{journalTag}', [JournalTagController::class, 'update'])->name('journal.tags.update');
    Route::delete('/{journalTag}', [JournalTagController::class, 'destroy'])->name('journal.tags.destroy');
});
