<?php

use Illuminate\Support\Facades\Route;

// ---------- Teacher dashboard ----------
Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teachers')
    ->name('teachers.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('teachers.dashboard');
        })->name('dashboard');
    });


