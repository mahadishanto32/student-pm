@extends('teachers.layouts.app')

@section('title', 'Edit Project Book')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Edit Project Book</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

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

                <form action="{{ route('teachers.project-books.update', $projectBook->id) }}"
                      method="POST" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project</label>
                                <input type="text" class="form-control"
                                       value="{{ $projectBook->project->group_number }} — {{ $projectBook->project->project_name ?? $projectBook->project->project_topic }}"
                                       readonly>
                                <small class="text-muted">Project cannot be changed.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    @foreach ($statuses as $s)
                                        <option value="{{ $s }}"
                                            @selected(old('status', $projectBook->status) === $s)>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Individual student marks: only visible when status = completed --}}
                    <div id="marks-section" style="display: none;">
                        <h4>Student Marks (out of {{ \App\Models\ProjectMark::MARKS_PER_TOPIC }})</h4>
                        <p class="text-muted">Required for every student when the status is Completed.</p>

                        @forelse ($students as $student)
                            @php
                                $pm    = $existingMarks[$student->id] ?? null;
                                $given = $pm?->distributions->first()?->given_marks;
                            @endphp
                            <div class="row" style="margin-bottom: 10px;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Student</label>
                                        <input type="text" class="form-control" value="{{ $student->name }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Marks (0 - {{ \App\Models\ProjectMark::MARKS_PER_TOPIC }})</label>
                                        <input type="number" name="marks[{{ $student->id }}][given_marks]"
                                               class="form-control" min="0"
                                               max="{{ \App\Models\ProjectMark::MARKS_PER_TOPIC }}" step="0.25"
                                               value="{{ old("marks.{$student->id}.given_marks", $given) }}">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <input type="text" name="marks[{{ $student->id }}][remarks]"
                                               class="form-control" placeholder="Optional remarks"
                                               value="{{ old("marks.{$student->id}.remarks", $pm?->remarks) }}">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No students found in this project's team.</p>
                        @endforelse

                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h4>Chapters</h4>
                            <p class="text-muted">Leave the title empty to remove a chapter.</p>
                        </div>
                    </div>

                    <div id="chapters-wrapper">
                        @php
                            $oldChapters = old('chapters', $projectBook->chapters->map(function ($c) {
                                return [
                                    'chapter_no'          => $c->chapter_no,
                                    'chapter_title'       => $c->chapter_title,
                                    'chapter_description' => $c->chapter_description,
                                ];
                            })->toArray());

                            if (empty($oldChapters)) {
                                $oldChapters = [['chapter_no' => 1, 'chapter_title' => '', 'chapter_description' => '']];
                            }
                        @endphp

                        @foreach ($oldChapters as $i => $chapter)
                            <div class="row chapter-row" style="margin-bottom: 10px;">
                                <div class="col-md-1">
                                    <input type="number" name="chapters[{{ $i }}][chapter_no]"
                                           class="form-control" placeholder="No"
                                           value="{{ $chapter['chapter_no'] ?? ($i + 1) }}" min="1">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="chapters[{{ $i }}][chapter_title]"
                                           class="form-control" placeholder="Chapter title"
                                           value="{{ $chapter['chapter_title'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="chapters[{{ $i }}][chapter_description]"
                                           class="form-control" placeholder="Description (optional)"
                                           value="{{ $chapter['chapter_description'] ?? '' }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-remove-chapter">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="add-chapter" class="btn btn-default">
                                <i class="fa fa-plus"></i> Add Chapter
                            </button>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Update
                            </button>
                            <a href="{{ route('teachers.project-books.index') }}" class="btn btn-default">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        let index = {{ count(old('chapters', $projectBook->chapters)) ?: 1 }};
        const wrapper = document.getElementById('chapters-wrapper');
        const addBtn  = document.getElementById('add-chapter');

        const statusSelect = document.getElementById('status');
        const marksSection = document.getElementById('marks-section');

        function toggleMarks() {
            const show = statusSelect.value === 'completed';
            marksSection.style.display = show ? '' : 'none';
            // Disabled inputs are not submitted or validated by the browser
            marksSection.querySelectorAll('input[name^="marks"]').forEach(el => el.disabled = !show);
        }
        statusSelect.addEventListener('change', toggleMarks);
        toggleMarks();

        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'row chapter-row';
            row.style.marginBottom = '10px';
            row.innerHTML = `
                <div class="col-md-1">
                    <input type="number" name="chapters[${index}][chapter_no]" class="form-control" value="${wrapper.querySelectorAll('.chapter-row').length + 1}" min="1">
                </div>
                <div class="col-md-4">
                    <input type="text" name="chapters[${index}][chapter_title]" class="form-control" placeholder="Chapter title">
                </div>
                <div class="col-md-6">
                    <input type="text" name="chapters[${index}][chapter_description]" class="form-control" placeholder="Description (optional)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-chapter">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `;
            wrapper.appendChild(row);
            index++;
        });

        wrapper.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-chapter');
            if (!btn) return;
            btn.closest('.chapter-row').remove();
        });
    })();
</script>
@endpush