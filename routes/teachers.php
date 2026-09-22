<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Teacher\TeacherProjectController;
use App\Http\Controllers\Teacher\TeacherProjectBookController;

Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teachers')
    ->name('teachers.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('teachers.dashboard');
        })->name('dashboard');

        Route::resource('projects', TeacherProjectController::class)
            ->only(['index', 'show', 'edit', 'update']);

        // Project Books — NO create / store
        Route::resource('project-books', TeacherProjectBookController::class)
            ->only(['index', 'show', 'edit', 'update', 'destroy']);
    });