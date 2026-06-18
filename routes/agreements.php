<?php

use App\Http\Controllers\Agreements\AgreementController;
use App\Http\Controllers\Agreements\InsightController;
use Illuminate\Support\Facades\Route;

Route::get('/agreements', [AgreementController::class, 'index'])->name('agreements.index');
Route::post('/agreements', [AgreementController::class, 'storeAgreement'])->name('agreements.store');
Route::delete('/agreements/{agreement}', [AgreementController::class, 'destroyAgreement'])->name('agreements.destroy');
Route::post('/agreements/{agreement}/check-in', [AgreementController::class, 'storeCheckIn'])->name('agreements.check-in');
Route::post('/goals', [AgreementController::class, 'storeGoal'])->name('goals.store');
Route::patch('/goals/{goal}', [AgreementController::class, 'updateGoal'])->name('goals.update');

Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');
Route::post('/insights', [InsightController::class, 'store'])->name('insights.store');
Route::delete('/insights/{insight}', [InsightController::class, 'destroy'])->name('insights.destroy');
Route::post('/insights/{insight}/resend', [InsightController::class, 'resend'])->name('insights.resend');
