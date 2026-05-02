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
            <h1>New Conversation</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li><a href="{{ route('scambaiter.index') }}">Scambaiter</a></li>
                <li>New Conversation</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="{{ route('scambaiter.store') }}">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">
            <div>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header"><h3>Conversation Details</h3></div>
                    <div class="card-body">
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Conversation Title <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="e.g. Nigerian Prince Scam" required>
                            @error('title')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Scammer's Name</label>
                            <input type="text" name="scammer_name" value="{{ old('scammer_name') }}" class="form-control" placeholder="e.g. Prince Emmanuel">
                            @error('scammer_name')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Default Subject</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" placeholder="e.g. Re: Urgent Business Proposal">
                            <p style="font-size: 0.75rem; color: #888; margin-top: 0.25rem;">Pre-filled on every email in this conversation. Can still be overridden per email.</p>
                            @error('subject')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Marcus's Occupation <span style="color: #888; font-weight: 400; font-size: 0.85rem;">in this conversation</span></label>
                            <input type="text" name="occupation" value="{{ old('occupation') }}" class="form-control" placeholder="e.g. Retired plumber, Wealthy widow, Lottery winner">
                            @error('occupation')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Character Profile</h3></div>
                    <div class="card-body">
                        <p style="font-size: 0.85rem; color: #888; margin-bottom: 1.25rem;">This profile locks once the first email is added to keep the character consistent.</p>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Intelligence Level <span style="color: #ef4444;">*</span></label>
                            <select name="intelligence_level" class="form-control">
                                @foreach($intelligenceLevels as $level)
                                    <option value="{{ $level->value }}" {{ old('intelligence_level', 'average') === $level->value ? 'selected' : '' }}>
                                        {{ $level->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('intelligence_level')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Personality Traits</label>
                            <input type="text" name="personality_traits" value="{{ old('personality_traits') }}" class="form-control" placeholder="e.g. paranoid, overly trusting, lonely, pedantic (comma separated)">
                            <p style="font-size: 0.75rem; color: #888; margin-top: 0.25rem;">Separate traits with commas</p>
                            @error('personality_traits')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Personal Details</label>
                            <textarea name="personal_details" class="form-control" rows="4" placeholder="Recurring people, pets, hobbies, running jokes to weave into the character...">{{ old('personal_details') }}</textarea>
                            @error('personal_details')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Writing Style Quirks</label>
                            <textarea name="writing_style" class="form-control" rows="4" placeholder="e.g. old fashioned, terrible with technology, overly formal, excessive use of ellipses...">{{ old('writing_style') }}</textarea>
                            @error('writing_style')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 0.75rem;">
                            <i class="fas fa-plus"></i> Create Conversation
                        </button>
                        <a href="{{ route('scambaiter.index') }}" class="btn btn-secondary" style="width: 100%;">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
