<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\TeacherProjectController;
use App\Http\Controllers\Teacher\TeacherProjectBookController;
use App\Http\Controllers\Teacher\TeacherMeetingController;
use App\Http\Controllers\Teacher\TeacherProjectMilestoneController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherPresentationController;

Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teachers')
    ->name('teachers.')
    ->group(function () {

        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        Route::resource('projects', TeacherProjectController::class)->only(['index', 'show', 'edit', 'update']);

        Route::resource('project-books', TeacherProjectBookController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);

        Route::resource('meetings', TeacherMeetingController::class);

        // ---------- Teacher: manage milestones for assigned projects (no create / store) ----------
        Route::resource('milestones', TeacherProjectMilestoneController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);

        Route::resource('presentations', TeacherPresentationController::class)->only(['index', 'show', 'edit', 'update']);
    });
