<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminProjectBookController;
use App\Http\Controllers\Admin\AdminMeetingController;
use App\Http\Controllers\Admin\AdminProjectMilestoneController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPresentationController;
use App\Http\Controllers\Admin\AdminVideoResumeController;

// ---------- Admin dashboard ----------
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::resource('projects', AdminProjectController::class)->except(['create', 'store']);
        Route::resource('project-books', AdminProjectBookController::class);
        Route::resource('meetings', AdminMeetingController::class);

        // ---------- Admin: manage milestones (full CRUD) ----------
        Route::resource('milestones', AdminProjectMilestoneController::class);
        Route::resource('presentations', AdminPresentationController::class);
        Route::resource('video-resumes', AdminVideoResumeController::class)->only(['index', 'show', 'edit', 'update']);
    });
