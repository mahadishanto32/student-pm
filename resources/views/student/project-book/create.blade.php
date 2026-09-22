@extends('layouts.app')

@section('title', 'Create Project Book')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Create Project Book</h2>
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

                <form action="{{ route('student.project-books.store') }}" method="POST" style="padding: 15px;">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control" required>
                                    <option value="">— Select Project —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            @selected(old('project_id') == $project->id)>
                                            {{ $project->group_number }} — {{ $project->project_name ?? $project->project_topic }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($projects->isEmpty())
                                    <small class="text-muted">All of your projects already have a book.</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h4>Chapters</h4>
                            <p class="text-muted">Add the chapters for this book. Leave the title empty to skip a row.</p>
                        </div>
                    </div>

                    <div id="chapters-wrapper">
                        @php
                            $oldChapters = old('chapters', [
                                ['chapter_no' => 1, 'chapter_title' => '', 'chapter_description' => ''],
                            ]);
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
                                <i class="fa fa-save"></i> Save
                            </button>
                            <a href="{{ route('student.project-books.index') }}" class="btn btn-default">
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
        let index = {{ count(old('chapters', [1])) }};
        const wrapper = document.getElementById('chapters-wrapper');
        const addBtn  = document.getElementById('add-chapter');

        function renumber() {
            // Optionally auto-update chapter_no fields based on position
            wrapper.querySelectorAll('.chapter-row').forEach((row, i) => {
                const noInput = row.querySelector('input[name$="[chapter_no]"]');
                if (noInput && (!noInput.value || noInput.dataset.auto === 'true')) {
                    noInput.value = i + 1;
                    noInput.dataset.auto = 'true';
                }
            });
        }

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
            renumber();
        });

        wrapper.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-chapter');
            if (!btn) return;
            btn.closest('.chapter-row').remove();
            renumber();
        });
    })();
</script>
@endpush