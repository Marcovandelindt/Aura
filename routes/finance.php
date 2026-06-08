<?php

use App\Http\Controllers\Finance\ExpenseCategoriesController;
use App\Http\Controllers\Finance\ExpensesController;
use App\Http\Controllers\Finance\ExpenseStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('expenses')->group(function () {
    Route::get('/', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::post('/', [ExpensesController::class, 'store'])->name('expenses.store');
    Route::get('/stats', [ExpenseStatsController::class, 'index'])->name('expenses.stats');
    Route::put('/{expense}', [ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('/{expense}', [ExpensesController::class, 'destroy'])->name('expenses.destroy');

    Route::prefix('categories')->group(function () {
        Route::get('/', [ExpenseCategoriesController::class, 'index'])->name('expenses.categories.index');
        Route::post('/', [ExpenseCategoriesController::class, 'store'])->name('expenses.categories.store');
        Route::put('/{category}', [ExpenseCategoriesController::class, 'update'])->name('expenses.categories.update');
        Route::delete('/{category}', [ExpenseCategoriesController::class, 'destroy'])->name('expenses.categories.destroy');
    });
});
