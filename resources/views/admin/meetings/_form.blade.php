@php
    $m = $meeting ?? null;
    $selectedPlatform = old('platform', $m->platform ?? 'physical');
    $selectedType     = old('type', $m->type ?? 'upcoming');
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="project_id">Project <span class="text-danger">*</span></label>
            <select name="project_id" id="project_id" class="form-control" required>
                <option value="">— Select Project —</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}"
                        @selected(old('project_id', $m->project_id ?? '') == $project->id)>
                        {{ $project->project_name }}
                        @if ($project->group_number)
                            (Group: {{ $project->group_number }})
                        @endif
                    </option>
                @endforeach
            </select>
            @error('project_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="title">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control"
                   value="{{ old('title', $m->title ?? '') }}" required>
            @error('title') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="meeting_date_and_time">Meeting Date &amp; Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="meeting_date_and_time" id="meeting_date_and_time"
                   class="form-control"
                   value="{{ old('meeting_date_and_time', optional($m?->meeting_date_and_time)->format('Y-m-d\TH:i')) }}"
                   required>
            @error('meeting_date_and_time') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="tentative_next_meeting_date_and_time">Tentative Next Meeting Date &amp; Time</label>
            <input type="datetime-local" name="tentative_next_meeting_date_and_time"
                   id="tentative_next_meeting_date_and_time" class="form-control"
                   value="{{ old('tentative_next_meeting_date_and_time', optional($m?->tentative_next_meeting_date_and_time)->format('Y-m-d\TH:i')) }}">
            @error('tentative_next_meeting_date_and_time') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="type">Type <span class="text-danger">*</span></label>
            <select name="type" id="type" class="form-control" required>
                @foreach ($types as $type)
                    <option value="{{ $type }}" @selected($selectedType === $type)>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="platform">Platform <span class="text-danger">*</span></label>
            <select name="platform" id="platform" class="form-control" required>
                @foreach ($platforms as $platform)
                    <option value="{{ $platform }}" @selected($selectedPlatform === $platform)>
                        {{ ucfirst($platform) }}
                    </option>
                @endforeach
            </select>
            @error('platform') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4"
                      placeholder="Agenda or meeting description...">{{ old('description', $m->description ?? '') }}</textarea>
            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="supervisor_note">Supervisor Note</label>
            <textarea name="supervisor_note" id="supervisor_note" class="form-control" rows="4"
                      placeholder="Notes from the supervisor...">{{ old('supervisor_note', $m->supervisor_note ?? '') }}</textarea>
            @error('supervisor_note') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
</div>