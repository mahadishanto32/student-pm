<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Role-based redirect
        return redirect()->intended($this->redirectPathFor(Auth::user()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Determine the post-login redirect path based on user role.
     */
    protected function redirectPathFor($user): string
    {
        return match ($user->role) {
            'admin'   => route('admin.dashboard',   absolute: false),
            'teacher' => route('teachers.dashboard', absolute: false),
            'student' => route('dashboard',         absolute: false),
            default   => route('dashboard',         absolute: false),
        };
    }
}
