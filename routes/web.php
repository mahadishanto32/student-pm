<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Student\StudentNotificationController;
use App\Http\Controllers\Teacher\TeacherNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ---------- Profile ----------
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('notifications/{id}/read', [AdminNotificationController::class, 'read'])->name('notifications.read');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('notifications.readAll');
    });
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('notifications/{id}/read', [StudentNotificationController::class, 'read'])->name('notifications.read');
        Route::post('notifications/read-all', [StudentNotificationController::class, 'readAll'])->name('notifications.readAll');
    });
    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('notifications/{id}/read', [TeacherNotificationController::class, 'read'])->name('notifications.read');
        Route::post('notifications/read-all', [TeacherNotificationController::class, 'readAll'])->name('notifications.readAll');
    });
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/teachers.php';
require __DIR__.'/students.php';
