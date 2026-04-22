@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Add Job Application</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('jobs.index') }}">Job Applications</a></li>
                    <li>Add</li>
                </ul>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="{{ route('jobs.store') }}">
                @csrf

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title">Job Title <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="e.g. Laravel Developer" autofocus>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="company">Company <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="company" name="company" class="form-control @error('company') is-invalid @enderror"
                           value="{{ old('company') }}" placeholder="e.g. Acme BV">
                    @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location') }}" placeholder="e.g. Mijdrecht">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                        <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', 'saved') === $status->value ? 'selected' : '' }}>
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
                           value="{{ old('url') }}" placeholder="https://linkedin.com/jobs/...">
                    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="applied_at">Applied On</label>
                    <input type="date" id="applied_at" name="applied_at" class="form-control @error('applied_at') is-invalid @enderror"
                           value="{{ old('applied_at') }}">
                    @error('applied_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                              rows="4" placeholder="Salary, requirements, contact person...">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
