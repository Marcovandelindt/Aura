<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

require __DIR__.'/on-this-day.php';
require __DIR__.'/spotify.php';
require __DIR__.'/strava.php';
require __DIR__.'/music.php';
require __DIR__.'/gaming.php';
require __DIR__.'/media.php';
require __DIR__.'/health.php';
require __DIR__.'/finance.php';
require __DIR__.'/journal.php';
require __DIR__.'/productivity.php';
require __DIR__.'/agreements.php';
require __DIR__.'/tasks.php';
require __DIR__.'/social.php';
require __DIR__.'/settings.php';
