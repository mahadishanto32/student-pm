<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

// ---------- Admin dashboard ----------
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');

        // ---------- Admin: manage users (CRUD) ----------
        Route::resource('users', AdminUserController::class)->except(['show']);

    });
