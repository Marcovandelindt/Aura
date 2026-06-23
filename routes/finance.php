<?php

use App\Http\Controllers\Finance\ExpenseCategoriesController;
use App\Http\Controllers\Finance\ExpensesController;
use App\Http\Controllers\Finance\ExpenseStatsController;
use App\Http\Controllers\Finance\MerchantsController;
use App\Http\Controllers\Finance\SubscriptionsController;
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

    Route::post('/merchants', [MerchantsController::class, 'store'])->name('expenses.merchants.store');

    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [SubscriptionsController::class, 'index'])->name('expenses.subscriptions.index');
        Route::post('/', [SubscriptionsController::class, 'store'])->name('expenses.subscriptions.store');
        Route::put('/{subscription}', [SubscriptionsController::class, 'update'])->name('expenses.subscriptions.update');
        Route::delete('/{subscription}', [SubscriptionsController::class, 'destroy'])->name('expenses.subscriptions.destroy');
    });
});
