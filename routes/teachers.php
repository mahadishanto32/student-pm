<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Teacher\TeacherProjectController;

// ---------- Teacher dashboard ----------
Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teachers')
    ->name('teachers.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('teachers.dashboard');
        })->name('dashboard');

        // ---------- Teacher: manage their assigned projects ----------
        Route::resource('projects', TeacherProjectController::class)
            ->only(['index', 'show', 'edit', 'update']);
    });