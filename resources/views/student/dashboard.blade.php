@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $r = fn ($name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : null;
    @endphp
    @include('dashboard.partials.assets')

    <div class="dx">
        <x-dx.hero
            :title="\App\Support\DashboardMetrics::greeting() . ', ' . Auth::user()->name"
            :text="$nextEnd
                ? ($nextEnd['daysLeft'] >= 0
                    ? $nextEnd['name'] . ' is due in ' . $nextEnd['daysLeft'] . ' ' . \Illuminate\Support\Str::plural('day', $nextEnd['daysLeft']) . '.'
                    : $nextEnd['name'] . ' is ' . abs($nextEnd['daysLeft']) . ' ' . \Illuminate\Support\Str::plural('day', abs($nextEnd['daysLeft'])) . ' past its end date.')
                : ($projects->isEmpty() ? 'You are not on a project yet. Your supervisor or an administrator will add you.' : now()->format('l, j F Y'))"
            :chips="[
                ['label' => 'milestones overdue', 'value' => $stats['overdue'],   'alert' => true],
                ['label' => 'due today',          'value' => $stats['due_today']],
                ['label' => 'unread notifications', 'value' => $notifications['unread']],
            ]"
            :actions="[
                ['label' => 'My milestones', 'url' => $r('student.milestones.index')],
                ['label' => 'Project book', 'url' => $r('student.project-books.index'), 'ghost' => true],
            ]" />

        <div class="row">
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="folder-open-o" tone="blue" :value="number_format($stats['projects'])" label="My projects"
                :note="$nextEnd && $nextEnd['daysLeft'] !== null ? ($nextEnd['daysLeft'] >= 0 ? $nextEnd['daysLeft'] . ' days to next deadline' : 'Past end date') : null" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="check-circle" tone="green" :value="$stats['completed'] . ' / ' . $stats['milestones']" label="Milestones completed"
                :note="$stats['completion'] . '% done'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="hourglass-half" tone="amber" :value="number_format($stats['pending'])" label="Pending milestones"
                :note="$stats['overdue'] > 0 ? $stats['overdue'] . ' overdue' : 'None overdue'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="book" tone="teal" :value="number_format($stats['books'])" label="Project books"
                :note="($bookStats['approved'] ?? 0) . ' approved, ' . ($bookStats['pending'] ?? 0) . ' pending'" /></div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @include('dashboard.partials.projects', [
                    'title' => 'My project', 'sub' => 'Your progress against the project timeline',
                    'href' => $r('student.projects.index'), 'showSupervisor' => true,
                    'emptyTitle' => 'You are not assigned to a project yet',
                ])
            </div>
            <div class="col-lg-4">@include('dashboard.partials.schedule')</div>
        </div>

        <div class="row">
            <div class="col-lg-8">@include('dashboard.partials.activity-chart', ['title' => 'My milestone activity'])</div>
            <div class="col-lg-4">@include('dashboard.partials.milestone-status')</div>
        </div>

        <div class="row">
            <div class="col-lg-4">@include('dashboard.partials.attention', ['href' => $r('student.milestones.index')])</div>
            <div class="col-lg-4">@include('dashboard.partials.updates', ['href' => $r('student.milestones.index')])</div>
            <div class="col-lg-4">@include('dashboard.partials.notifications')</div>
        </div>

        <div class="row">
            <div class="col-lg-6">@include('dashboard.partials.status-bars', ['title' => 'Project book status', 'counts' => $bookStats, 'emptyTitle' => 'No project book yet'])</div>
            <div class="col-lg-6">
                @include('dashboard.partials.marks-student')
            </div>
        </div>
    </div>
@endsection
