<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Student Project Management System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style></style>
    @endif
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] antialiased">

    <!-- Navbar -->
    <header class="w-full border-b border-[#19140015] dark:border-[#3E3E3A] sticky top-0 z-50 bg-[#FDFDFC]/80 dark:bg-[#0a0a0a]/80 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between">

            <!-- Logo + Site Name -->
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo/logo.png') }}" alt="{{ config('app.name') }} Logo" class="h-12 w-16 object-contain">
                <span class="font-semibold text-lg leading-none tracking-tight">
                    {{ config('app.name', 'Student Project Management System') }}
                </span>
            </a>

            <!-- Auth Links -->
            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-block px-5 py-2 bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18] rounded-md text-sm font-medium hover:opacity-90 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-block px-4 py-2 text-sm font-medium border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-md transition">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-block px-5 py-2 bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18] rounded-md text-sm font-medium hover:opacity-90 transition">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-8 pt-20 pb-24 text-center">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-[#1b1b18]/5 dark:bg-white/10 mb-6">
            Built for Students & Supervisors
        </span>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight max-w-3xl mx-auto">
            Manage Student Projects, <span class="text-blue-600 dark:text-blue-400">From Proposal to Defense</span>
        </h1>
        <p class="mt-6 text-lg text-[#1b1b18]/70 dark:text-[#EDEDEC]/70 max-w-2xl mx-auto">
            A centralized platform for students, supervisors, and admins to track project progress,
            manage deadlines, share resources, and collaborate — all in one place.
        </p>
        <div class="mt-10 flex items-center justify-center gap-4">
            @if (Route::has('register') && !Auth::check())
                <a href="{{ route('register') }}"
                   class="px-6 py-3 bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18] rounded-md font-medium hover:opacity-90 transition">
                    Get Started Free
                </a>
                <a href="{{ route('login') }}"
                   class="px-6 py-3 border border-[#19140035] dark:border-[#3E3E3A] rounded-md font-medium hover:border-[#1915014a] dark:hover:border-[#62605b] transition">
                    Log in
                </a>
            @elseif (Auth::check())
                <a href="{{ url('/dashboard') }}"
                   class="px-6 py-3 bg-[#1b1b18] text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18] rounded-md font-medium hover:opacity-90 transition">
                    Go to Dashboard
                </a>
            @endif
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-[#1b1b18]/[0.02] dark:bg-white/[0.03] py-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <h2 class="text-3xl font-bold tracking-tight">Everything You Need to Run Projects Smoothly</h2>
                <p class="mt-3 text-[#1b1b18]/70 dark:text-[#EDEDEC]/70">
                    Purpose-built tools that keep every project on track and every stakeholder in the loop.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $features = [
                        ['icon' => '📋', 'title' => 'Project Proposals', 'desc' => 'Submit, review, and approve project proposals with a clear digital workflow.'],
                        ['icon' => '✅', 'title' => 'Task Tracking', 'desc' => 'Break projects into milestones and tasks, and track completion in real time.'],
                        ['icon' => '👥', 'title' => 'Team Collaboration', 'desc' => 'Group members, supervisors, and reviewers work together in one shared space.'],
                        ['icon' => '📅', 'title' => 'Deadline Management', 'desc' => 'Automated reminders keep submissions, reviews, and defenses on schedule.'],
                        ['icon' => '📂', 'title' => 'Document Sharing', 'desc' => 'Upload reports, source code, and resources with version history.'],
                        ['icon' => '📊', 'title' => 'Progress Reports', 'desc' => 'Supervisors get dashboards showing real-time progress across every group.'],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <div class="bg-white dark:bg-[#141413] rounded-xl p-6 border border-[#19140015] dark:border-[#3E3E3A] hover:shadow-md transition">
                        <div class="text-3xl mb-4">{{ $feature['icon'] }}</div>
                        <h3 class="font-semibold text-lg mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-sm text-[#1b1b18]/70 dark:text-[#EDEDEC]/70">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="max-w-7xl mx-auto px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-3xl font-bold tracking-tight">How It Works</h2>
            <p class="mt-3 text-[#1b1b18]/70 dark:text-[#EDEDEC]/70">
                A simple flow from idea to final submission.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
                $steps = [
                    ['step' => '01', 'title' => 'Register', 'desc' => 'Students and supervisors create an account.'],
                    ['step' => '02', 'title' => 'Submit Proposal', 'desc' => 'Groups submit project ideas for approval.'],
                    ['step' => '03', 'title' => 'Track Progress', 'desc' => 'Manage tasks, milestones, and files together.'],
                    ['step' => '04', 'title' => 'Final Evaluation', 'desc' => 'Supervisors review, grade, and close out the project.'],
                ];
            @endphp

            @foreach ($steps as $s)
                <div>
                    <div class="text-4xl font-bold text-[#1b1b18]/10 dark:text-white/10 mb-3">{{ $s['step'] }}</div>
                    <h3 class="font-semibold mb-1">{{ $s['title'] }}</h3>
                    <p class="text-sm text-[#1b1b18]/70 dark:text-[#EDEDEC]/70">{{ $s['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- CTA Section -->
    @if (Route::has('register') && !Auth::check())
        <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-24">
            <div class="bg-[#1b1b18] dark:bg-white/5 text-white rounded-2xl px-8 py-16 text-center">
                <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-white/70 max-w-xl mx-auto mb-8">
                    Join your department's project management system and keep every submission organized.
                </p>
                <a href="{{ route('register') }}"
                   class="inline-block px-6 py-3 bg-white text-[#1b1b18] rounded-md font-medium hover:opacity-90 transition">
                    Create an Account
                </a>
            </div>
        </section>
    @endif

    <!-- Footer -->
    <footer class="border-t border-[#19140015] dark:border-[#3E3E3A] py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-[#1b1b18]/60 dark:text-[#EDEDEC]/60">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo/logo.png') }}" alt="Logo" class="h-6 w-6 object-contain">
                <span>{{ config('app.name', 'Student Project Management System') }} © {{ date('Y') }}</span>
            </div>
            <span>All rights reserved.</span>
        </div>
    </footer>

</body>
</html>
