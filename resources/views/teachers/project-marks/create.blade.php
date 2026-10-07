@extends('teachers.layouts.app')

@section('title', 'Give Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Give Marks</h2>
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
                        <h4>Individual Student Marking</h4>
                    </div>
                </div>

                <form action="{{ route('teachers.project-marks.store') }}" method="POST" style="padding: 15px;">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control" required>
                                    <option value="">— Select Project —</option>
                                    @foreach ($projectData as $p)
                                        <option value="{{ $p['id'] }}" @selected((string) old('project_id') === (string) $p['id'])>
                                            {{ $p['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_id">Student <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_id" class="form-control" required>
                                    <option value="">— Select Project First —</option>
                                </select>
                                <small class="text-muted">Students who already have marks for this project are disabled (use Edit instead).</small>
                            </div>
                        </div>
                    </div>

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
                                                   value="{{ old("marks.$i") }}">
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
                                  placeholder="Optional remarks for this student">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Marks</button>
                    <a href="{{ route('teachers.project-marks.index') }}" class="btn btn-default">Cancel</a>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const projects = @json($projectData);
    const oldStudent = @json((string) old('student_id'));
    const projectSelect = document.getElementById('project_id');
    const studentSelect = document.getElementById('student_id');

    function loadStudents() {
        const project = projects.find(p => String(p.id) === projectSelect.value);
        studentSelect.innerHTML = '';

        if (!project) {
            studentSelect.innerHTML = '<option value="">— Select Project First —</option>';
            return;
        }

        studentSelect.insertAdjacentHTML('beforeend', '<option value="">— Select Student —</option>');
        project.students.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name + (s.marked ? ' (already marked)' : '');
            opt.disabled = s.marked;
            if (String(s.id) === oldStudent) opt.selected = true;
            studentSelect.appendChild(opt);
        });
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.mark-input').forEach(i => total += parseFloat(i.value) || 0);
        document.getElementById('total_given').textContent = Math.round(total * 100) / 100;
    }

    projectSelect.addEventListener('change', loadStudents);
    document.querySelectorAll('.mark-input').forEach(i => i.addEventListener('input', updateTotal));
    loadStudents();
    updateTotal();
</script>
@endpush
