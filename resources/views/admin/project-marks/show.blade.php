@extends('admin.layouts.app')

@section('title', 'Project Marks Details')

@section('content')
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
                        <p class="text-muted" style="margin-bottom: 0;">
                            {{ $project->project_topic ?? '' }}<br>
                            Supervisor: <strong>{{ $project->teacher->name ?? '—' }}</strong>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('admin.project-marks.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                @include('shared.project-marks.sheet', ['showSupervisor' => true])

            </div>
        </div>
    </div>
@endsection