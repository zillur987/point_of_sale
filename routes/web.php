<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::redirect('/backend', '/backend/login');
Route::view('/backend/{any?}', 'app')->where('any', '.*');

// Website
// Route::view('/', 'home');