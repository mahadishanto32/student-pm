@extends('teachers.layouts.app')

@section('title', 'Teachers Dashboard')

@section('content')
    @php
        $r = fn ($name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : null;
    @endphp
    @include('dashboard.partials.assets')

    <div class="dx">
        <x-dx.hero
            :title="\App\Support\DashboardMetrics::greeting() . ', ' . Auth::user()->name"
            :text="now()->format('l, j F Y') . '. ' . ($stats['projects'] > 0 ? 'Your supervision queue is below.' : 'Projects will appear here once you are assigned as supervisor.')"
            :chips="[
                ['label' => 'milestones overdue', 'value' => $stats['overdue'],   'alert' => true],
                ['label' => 'due today',          'value' => $stats['due_today']],
                ['label' => 'students not yet marked', 'value' => $stats['students'] - $stats['marked']],
                ['label' => 'unread notifications', 'value' => $notifications['unread']],
            ]"
            :actions="[
                ['label' => 'Milestones', 'url' => $r('teachers.milestones.index')],
                ['label' => 'My projects', 'url' => $r('teachers.projects.index'), 'ghost' => true],
            ]" />

        <div class="row">
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="folder-open-o" tone="blue" :value="number_format($stats['projects'])" label="Supervised projects" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="graduation-cap" tone="teal" :value="number_format($stats['students'])" label="Students"
                :note="$stats['marked'] . ' of ' . $stats['students'] . ' marked'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="hourglass-half" tone="amber" :value="number_format($stats['pending'])" label="Pending milestones"
                :note="$stats['overdue'] > 0 ? $stats['overdue'] . ' overdue' : 'None overdue'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="book" tone="green" :value="number_format($stats['books'])" label="Project books"
                :note="($bookStats['pending'] ?? 0) . ' pending review'" /></div>
        </div>

        <div class="row">
            <div class="col-lg-8">@include('dashboard.partials.activity-chart', ['title' => 'Milestone activity on your projects'])</div>
            <div class="col-lg-4">@include('dashboard.partials.milestone-status')</div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @include('dashboard.partials.projects', [
                    'title' => 'My projects', 'sub' => 'Progress against time, per group',
                    'href' => $r('teachers.projects.index'), 'showSupervisor' => false,
                    'emptyTitle' => 'No assigned projects yet', 'emptyText' => 'An administrator can assign you as supervisor.',
                ])
            </div>
            <div class="col-lg-4">@include('dashboard.partials.schedule')</div>
        </div>

        <div class="row">
            <div class="col-lg-4">@include('dashboard.partials.attention', ['href' => $r('teachers.milestones.index')])</div>
            <div class="col-lg-4">@include('dashboard.partials.updates', ['href' => $r('teachers.milestones.index'), 'byWhom' => true])</div>
            <div class="col-lg-4">@include('dashboard.partials.notifications')</div>
        </div>

        <div class="row">
            <div class="col-lg-6">@include('dashboard.partials.status-bars', ['title' => 'Project books by status', 'sub' => 'Across your projects', 'counts' => $bookStats])</div>
            <div class="col-lg-6">
                <x-dx.panel title="Marking" :sub="$stats['marked'] . ' of ' . $stats['students'] . ' students marked'">
                    @forelse($marking as $s)
                        <div class="dx-plain">
                            <span>{{ $s['name'] }} <small class="text-muted">{{ $s['project'] }}</small></span>
                            <span class="dx-pill dx-st-{{ $s['marked'] ? 'completed' : 'pending' }}">{{ $s['marked'] ? 'Marked' : 'Not marked' }}</span>
                        </div>
                    @empty
                        <x-dx.empty icon="graduation-cap" title="No students yet" />
                    @endforelse
                </x-dx.panel>
            </div>
            <div class="row">
                <div class="col-md-12">@include('dashboard.partials.marks-teacher')</div>
            </div>
        </div>
    </div>
@endsection
