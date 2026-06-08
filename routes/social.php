<?php

use App\Http\Controllers\Social\PersonController;
use App\Http\Controllers\Social\ScambaiterController;
use App\Http\Controllers\Social\ScambaiterConversationController;
use App\Http\Controllers\Social\ScambaiterEmailController;
use App\Http\Controllers\Social\ScambaiterSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');

Route::prefix('scambaiter')->group(function () {
    Route::get('/', [ScambaiterController::class, 'index'])->name('scambaiter.index');
    Route::get('/profile-generator', [ScambaiterController::class, 'profileGenerator'])->name('scambaiter.profile-generator');
    Route::get('/settings', [ScambaiterSettingsController::class, 'edit'])->name('scambaiter.settings');
    Route::put('/settings', [ScambaiterSettingsController::class, 'update'])->name('scambaiter.settings.update');
    Route::get('/create', [ScambaiterConversationController::class, 'create'])->name('scambaiter.create');
    Route::post('/', [ScambaiterConversationController::class, 'store'])->name('scambaiter.store');
    Route::get('/{conversation}', [ScambaiterController::class, 'show'])->name('scambaiter.show');
    Route::get('/{conversation}/edit', [ScambaiterConversationController::class, 'edit'])->name('scambaiter.edit');
    Route::put('/{conversation}', [ScambaiterConversationController::class, 'update'])->name('scambaiter.update');
    Route::delete('/{conversation}', [ScambaiterConversationController::class, 'destroy'])->name('scambaiter.destroy');
    Route::get('/{conversation}/export', [ScambaiterController::class, 'export'])->name('scambaiter.export');
    Route::post('/{conversation}/emails', [ScambaiterEmailController::class, 'store'])->name('scambaiter.emails.store');
    Route::delete('/{conversation}/emails/{email}', [ScambaiterEmailController::class, 'destroy'])->name('scambaiter.emails.destroy');
});
