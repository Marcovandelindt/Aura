@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Edit Job Application</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('jobs.index') }}">Job Applications</a></li>
                    <li>Edit</li>
                </ul>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="{{ route('jobs.update', $jobApplication) }}">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title">Job Title <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $jobApplication->title) }}" autofocus>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="company">Company <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="company" name="company" class="form-control @error('company') is-invalid @enderror"
                           value="{{ old('company', $jobApplication->company) }}">
                    @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location', $jobApplication->location) }}">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                        <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', $jobApplication->status->value) === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="url">LinkedIn / Job URL</label>
                    <input type="url" id="url" name="url" class="form-control @error('url') is-invalid @enderror"
                           value="{{ old('url', $jobApplication->url) }}">
                    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="applied_at">Applied On</label>
                    <input type="date" id="applied_at" name="applied_at" class="form-control @error('applied_at') is-invalid @enderror"
                           value="{{ old('applied_at', $jobApplication->applied_at?->format('Y-m-d')) }}">
                    @error('applied_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                              rows="4">{{ old('notes', $jobApplication->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
