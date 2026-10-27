@extends('admin.layouts.app')

@section('title', 'Project Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Marks</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-12">
                        <h4>Marks of All Projects</h4>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('admin.project-marks.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search group no, name, topic, or class ID"
                                    value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                @include('shared.project-marks.summary-table', [
                    'routePrefix' => 'admin.project-marks',
                    'canEdit'     => false,
                    'showTeacher' => true,
                ])

                <div style="padding: 0 15px 15px 15px;">
                    {{ $projects->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection