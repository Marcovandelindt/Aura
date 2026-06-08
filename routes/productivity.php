<?php

use App\Http\Controllers\Productivity\IdeasController;
use App\Http\Controllers\Productivity\JobApplicationController;
use App\Http\Controllers\Productivity\WeeklyController;
use Illuminate\Support\Facades\Route;

Route::get('/weekly', [WeeklyController::class, 'index'])->name('weekly.index');

Route::prefix('ideas')->group(function () {
    Route::get('/', [IdeasController::class, 'index'])->name('ideas.index');
    Route::post('/', [IdeasController::class, 'store'])->name('ideas.store');
    Route::post('/{idea}/toggle', [IdeasController::class, 'toggle'])->name('ideas.toggle');
    Route::put('/{idea}', [IdeasController::class, 'update'])->name('ideas.update');
    Route::delete('/{idea}', [IdeasController::class, 'destroy'])->name('ideas.destroy');
});

Route::resource('jobs', JobApplicationController::class)->except(['show'])->parameters(['jobs' => 'jobApplication']);
