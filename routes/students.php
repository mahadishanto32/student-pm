<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentProjectController;
use App\Http\Controllers\Student\StudentProjectBookController;
use App\Http\Controllers\Student\StudentMeetingController;
use App\Http\Controllers\Student\StudentProjectMilestoneController;
use App\Http\Controllers\Student\StudentDashboardController;

// ---------- Student / default dashboard ----------
Route::get('/dashboard', [StudentDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ---------- Student: manage their assigned projects ----------
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('students')
    ->name('student.')
    ->group(function () {

    Route::resource('projects', StudentProjectController::class)
        ->only(['index', 'show', 'edit', 'update']);

    // Project Books (full CRUD)
    Route::resource('project-books', StudentProjectBookController::class);

    // Meetings — READ ONLY (no create / store / edit / update / destroy)
    Route::resource('meetings', StudentMeetingController::class)->only(['index', 'show']);

    // ---------- Milestones (full CRUD) ----------
    Route::resource('milestones', StudentProjectMilestoneController::class);
});