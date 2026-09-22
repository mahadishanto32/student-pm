@extends('admin.layouts.app')

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

                <form action="{{ route('admin.project-books.update', $projectBook->id) }}"
                      method="POST" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id">Project (Student Team) <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control" required>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            @selected(old('project_id', $projectBook->project_id) == $project->id)>
                                            {{ $project->group_number }} —
                                            {{ $project->project_name ?? $project->project_topic ?? 'Untitled' }}
                                            @if ($project->teamMembers->isNotEmpty())
                                                ({{ $project->teamMembers->pluck('name')->join(', ') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
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
                            <a href="{{ route('admin.project-books.index') }}" class="btn btn-default">
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