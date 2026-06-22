<?php

use App\Http\Controllers\OnThisDayController;
use Illuminate\Support\Facades\Route;

Route::get('/op-deze-dag', [OnThisDayController::class, 'index'])->name('on-this-day.index');
