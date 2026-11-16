@extends('layouts.app')

@section('title', 'Project Marks Details')

@section('content')
    @php
        use App\Models\MarksDistribution;

        $completed  = ($project->status ?? null) === 'completed';
        $shownTopics = $completed
            ? array_values($topicMap)
            : ['Milestones'];
        // Build a topic map containing only the visible topics
        $sheetTopicMap = collect($topicMap)
            ->filter(fn ($t) => in_array($t, $shownTopics, true))
            ->toArray();
    @endphp

    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Marks Details</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                <div class="row" style="padding: 15px;">
                    <div class="col-md-8">
                        <h4>
                            {{ $project->project_name ?? 'Untitled Project' }}
                            <small class="text-muted">(Group {{ $project->group_number ?? '—' }})</small>
                        </h4>
                        <p class="text-muted" style="margin-bottom: 0;">{{ $project->project_topic ?? '' }}</p>

                        @if (! $completed)
                            <div class="alert alert-info" style="margin-top: 10px;">
                                <i class="fa fa-lock"></i>
                                Project Book and Presentation marks will be visible once the project is marked
                                <strong>completed</strong>. Until then, you can see your Milestones score.
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('student.project-marks.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                {{-- Pass only the visible topics --}}
                @include('shared.project-marks.sheet', [
                    'topicMap'       => $sheetTopicMap,
                    'partial'        => ! $completed,
                    'showSupervisor' => false,
                ])

            </div>
        </div>
    </div>
@endsection