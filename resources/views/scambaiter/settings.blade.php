@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <a href="{{ route('scambaiter.index') }}" class="back-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                    </svg>
                    Back to Scambaiter
                </a>
            </div>
            <h1>Global Character Profile</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li><a href="{{ route('scambaiter.index') }}">Scambaiter</a></li>
                <li>Character Profile</li>
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('scambaiter.settings.update') }}">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">
            <div class="card">
                <div class="card-header">
                    <h3>Fixed Identity</h3>
                </div>
                <div class="card-body">
                    <p style="font-size: 0.85rem; color: #888; margin-bottom: 1.5rem;">
                        This is the baseline identity included in every export. Fill it in once and it stays consistent across all conversations.
                    </p>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Full Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name', $profile->full_name) }}" class="form-control" placeholder="e.g. Harold Pemberton" required>
                            @error('full_name')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Age</label>
                            <input type="number" name="age" value="{{ old('age', $profile->age) }}" class="form-control" placeholder="e.g. 74" min="1" max="120">
                            @error('age')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth?->format('Y-m-d')) }}" class="form-control">
                            @error('date_of_birth')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nationality</label>
                            <input type="text" name="nationality" value="{{ old('nationality', $profile->nationality) }}" class="form-control" placeholder="e.g. British">
                            @error('nationality')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Location</label>
                        <input type="text" name="location" value="{{ old('location', $profile->location) }}" class="form-control" placeholder="e.g. Bournemouth, UK">
                        @error('location')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Additional Facts</label>
                        <textarea name="additional_facts" class="form-control" rows="5" placeholder="Any other biographical details, quirks, or facts about this character that should always be included...">{{ old('additional_facts', $profile->additional_facts) }}</textarea>
                        @error('additional_facts')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i> Save Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
