@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    @php
        $r = fn ($name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : null;
    @endphp
    @include('dashboard.partials.assets')

    <div class="dx">
        <x-dx.hero
            :title="\App\Support\DashboardMetrics::greeting() . ', ' . Auth::user()->name"
            :text="now()->format('l, j F Y') . '. Here is where the project system stands today.'"
            :chips="[
                ['label' => 'milestones overdue',  'value' => $stats['overdue'],   'alert' => true],
                ['label' => 'due today',           'value' => $stats['due_today']],
                ['label' => 'projects without a supervisor', 'value' => $unassigned, 'alert' => true],
                ['label' => 'unread notifications','value' => $notifications['unread']],
            ]"
            :actions="[
                ['label' => 'Milestones', 'url' => $r('admin.milestones.index')],
                ['label' => 'Projects',   'url' => $r('admin.projects.index'), 'ghost' => true],
            ]" />

        {{-- KPIs --}}
        <div class="row">
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="users" tone="blue" :value="number_format($stats['users'])" label="Users"
                :note="($roles['student'] ?? 0) . ' students, ' . ($roles['teacher'] ?? 0) . ' teachers'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="folder-open-o" tone="teal" :value="number_format($stats['projects'])" label="Projects"
                :note="$unassigned > 0 ? $unassigned . ' need a supervisor' : 'All have a supervisor'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="book" tone="green" :value="number_format($stats['books'])" label="Project books"
                :note="($bookStats['approved'] ?? 0) . ' approved'" /></div>
            <div class="col-sm-6 col-xl-3"><x-dx.kpi icon="flag-checkered" tone="amber" :value="number_format($stats['milestones'])" label="Milestones"
                :note="$stats['completion'] . '% completed'" /></div>
        </div>

        {{-- Trends --}}
        <div class="row">
            <div class="col-lg-8">@include('dashboard.partials.activity-chart', ['title' => 'System activity'])</div>
            <div class="col-lg-4">@include('dashboard.partials.milestone-status')</div>
        </div>

        {{-- Projects + schedule --}}
        <div class="row">
            <div class="col-lg-8">
                @include('dashboard.partials.projects', [
                    'title' => 'Recent projects', 'sub' => 'Newest first, with progress against time',
                    'href' => $r('admin.projects.index'), 'showSupervisor' => true,
                    'emptyTitle' => 'No projects have been created yet',
                ])
            </div>
            <div class="col-lg-4">@include('dashboard.partials.schedule')</div>
        </div>

        {{-- Work queues --}}
        <div class="row">
            <div class="col-lg-4">@include('dashboard.partials.attention', ['href' => $r('admin.milestones.index')])</div>
            <div class="col-lg-4">@include('dashboard.partials.updates', ['href' => $r('admin.milestones.index'), 'byWhom' => true])</div>
            <div class="col-lg-4">@include('dashboard.partials.notifications')</div>
        </div>

        {{-- Breakdowns --}}
        <div class="row">
            <div class="col-lg-4">@include('dashboard.partials.status-bars', ['title' => 'Projects by status', 'counts' => $projectStatus])</div>
            <div class="col-lg-4">@include('dashboard.partials.status-bars', ['title' => 'Project books by status', 'counts' => $bookStats])</div>
            <div class="col-lg-4">
                <x-dx.panel title="People and workload">
                    @forelse($workload as $w)
                        <div class="dx-plain"><span>{{ $w->name }}</span><b>{{ $w->projects }} {{ \Illuminate\Support\Str::plural('project', $w->projects) }}</b></div>
                    @empty
                        <x-dx.empty icon="user-o" title="No supervisors assigned yet" />
                    @endforelse
                    <div class="dx-plain" style="margin-top:12px"><span>Presentations</span><b>{{ $submissions['presentations'] }}</b></div>
                    <div class="dx-plain"><span>Video resumes</span><b>{{ $submissions['videos'] }}</b></div>
                    <div class="dx-plain"><span>Meetings held or scheduled</span><b>{{ $submissions['meetings'] }}</b></div>
                    
                </x-dx.panel>
            </div>
        </div>
        <div class="row">
            <div class="col-12">@include('dashboard.partials.marks-admin')</div>
        </div>
    </div>
@endsection
