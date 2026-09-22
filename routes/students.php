<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentProjectController;
use App\Http\Controllers\Student\StudentProjectBookController;

// ---------- Student / default dashboard ----------
Route::get('/dashboard', function () {
    return view('student.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ---------- Student: manage their assigned projects ----------
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('students')
    ->name('student.')
    ->group(function () {

    Route::resource('projects', StudentProjectController::class)->only(['index', 'show', 'edit', 'update']);

    // Project Books (full CRUD)
    Route::resource('project-books', StudentProjectBookController::class);
});