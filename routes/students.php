<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentProjectController;
use App\Http\Controllers\Student\StudentProjectBookController;
use App\Http\Controllers\Student\StudentMeetingController;
use App\Http\Controllers\Student\StudentProjectMilestoneController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentPresentationController;
use App\Http\Controllers\Student\StudentVideoResumeController;
use App\Http\Controllers\Student\StudentProjectMarkController;

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
        ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    // Project Books (full CRUD)
    Route::resource('project-books', StudentProjectBookController::class);

    // Meetings — READ ONLY
    Route::resource('meetings', StudentMeetingController::class)->only(['index', 'show']);

    // Milestones (full CRUD)
    Route::resource('milestones', StudentProjectMilestoneController::class);

    // Presentations (full CRUD)
    Route::resource('presentations', StudentPresentationController::class);

    // Video Resumes (full CRUD)
    Route::resource('video-resumes', StudentVideoResumeController::class);

    // Project Marks — READ ONLY for students
    Route::resource('project-marks', StudentProjectMarkController::class)
        ->only(['index', 'show']);
});
