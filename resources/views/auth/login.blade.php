<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} — Log in</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

        {{-- Logo / Brand --}}
        <div class="mb-8 text-center">
            <a href="/" class="inline-flex items-center gap-2">
                <x-application-logo class="w-12 h-12 fill-current text-indigo-600" />
                <span class="text-2xl font-bold text-gray-800">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </a>
        </div>

        {{-- Login Card --}}
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-gray-200 p-8">

            <div class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-800">Welcome back</h1>
                <p class="mt-1 text-sm text-gray-500">Sign in to your account to continue</p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input
                        id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input
                        id="password"
                        class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember + Forgot --}}
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input
                            id="remember_me"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            name="remember"
                        >
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            class="text-sm text-indigo-600 hover:text-indigo-800 underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-md"
                            href="{{ route('password.request') }}"
                        >
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                {{-- Submit --}}
                <x-primary-button class="w-full justify-center py-3 text-base">
                    {{ __('Log in') }}
                </x-primary-button>
            </form>
        </div>

        {{-- Footer note --}}
        <p class="mt-8 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
