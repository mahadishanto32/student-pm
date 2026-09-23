@extends('teachers.layouts.app')

@section('title', 'Edit Meeting')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Edit Meeting</h2>
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

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-6">
                        <h4>Edit Meeting: {{ $meeting->title }}</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('teachers.meetings.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Meetings
                        </a>
                    </div>
                </div>

                <div style="padding: 15px;">
                    <form action="{{ route('teachers.meetings.update', $meeting->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('teachers.meetings._form', ['meeting' => $meeting])

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Meeting
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection