@extends('teachers.layouts.app')

@section('title', 'New Meeting')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>New Meeting</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($projects->isEmpty())
                    <div class="alert alert-warning" style="margin: 15px;">
                        You have no projects assigned yet, so you cannot create a meeting.
                    </div>
                @endif

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-6">
                        <h4>Create Meeting</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('teachers.meetings.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Meetings
                        </a>
                    </div>
                </div>

                <div style="padding: 15px;">
                    <form action="{{ route('teachers.meetings.store') }}" method="POST">
                        @csrf

                        @include('teachers.meetings._form', ['meeting' => null])

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-success" @disabled($projects->isEmpty())>
                                <i class="fa fa-save"></i> Create Meeting
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection