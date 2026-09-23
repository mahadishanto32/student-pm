<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} — Register</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

        {{-- Logo / Brand --}}
        <div class="mb-8 text-center">
            <a href="/" class="inline-flex flex-col items-center gap-3">
                <img
                    src="{{ asset('images/logo/logo_icon.png') }}"
                    alt="{{ config('app.name', 'Laravel') }}"
                    class="w-16 h-16 object-contain drop-shadow-sm"
                >
                <span class="text-2xl font-bold text-gray-800 tracking-tight">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </a>
        </div>

        {{-- Register Card --}}
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-gray-200 p-8">

            <div class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-800">Create an account</h1>
                <p class="mt-1 text-sm text-gray-500">Sign up to get started</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input
                        id="name"
                        class="block mt-1 w-full"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Student Name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                {{-- Email Address --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input
                        id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
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
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Confirm Password --}}
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input
                        id="password_confirmation"
                        class="block mt-1 w-full"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                {{-- Submit --}}
                <x-primary-button class="w-full justify-center py-3 text-base">
                    {{ __('Register') }}
                </x-primary-button>
            </form>

            {{-- Divider + login link --}}
            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    {{ __('Already registered?') }}
                    
                      <a class="text-indigo-600 hover:text-indigo-800 font-medium underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-md"
                        href="{{ route('login') }}"
                    >
                        {{ __('Log in') }}
                    </a>
                </p>
            </div>
        </div>

        {{-- Footer note --}}
        <p class="mt-8 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
        </p>
    </div>
</body>
</html>