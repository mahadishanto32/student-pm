@extends('teachers.layouts.app')

@section('title', 'Edit Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Edit Marks</h2>
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
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin-bottom:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-12">
                        <h4>
                            {{ $mark->student->name ?? '—' }}
                            <small>
                                — Group {{ $mark->project->group_number ?? '—' }}
                                ({{ $mark->project->project_name ?? 'Untitled' }})
                            </small>
                        </h4>
                    </div>
                </div>

                <form action="{{ route('teachers.project-marks.update', $mark->id) }}" method="POST" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Topic</th>
                                    <th style="width:200px;">Given Marks</th>
                                    <th style="width:120px;">Out Of</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topics as $i => $topic)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $topic }}</td>
                                        <td>
                                            <input type="number" name="marks[{{ $i }}]" class="form-control mark-input"
                                                   min="0" max="{{ $outOf }}" step="0.01" required
                                                   value="{{ old("marks.$i", isset($distributions[$topic]) ? (float) $distributions[$topic]->given_marks : '') }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" value="{{ $outOf }}" readonly>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Total</th>
                                    <th><span id="total_given">0</span></th>
                                    <th>{{ $outOf * count($topics) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="4" class="form-control"
                                  placeholder="Optional remarks for this student">{{ old('remarks', $mark->remarks) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Marks</button>
                    <a href="{{ route('teachers.project-marks.show', $mark->id) }}" class="btn btn-default">Cancel</a>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.mark-input').forEach(i => total += parseFloat(i.value) || 0);
        document.getElementById('total_given').textContent = Math.round(total * 100) / 100;
    }
    document.querySelectorAll('.mark-input').forEach(i => i.addEventListener('input', updateTotal));
    updateTotal();
</script>
@endpush
