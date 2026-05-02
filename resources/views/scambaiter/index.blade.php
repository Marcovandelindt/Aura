@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Scambaiter</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Scambaiter</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('scambaiter.settings') }}" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Character Profile
                </a>
                <a href="{{ route('scambaiter.profile-generator') }}" class="btn btn-secondary">
                    <i class="fas fa-id-card"></i> Generate Character Profile
                </a>
                <a href="{{ route('scambaiter.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Conversation
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    @if($conversations->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 4rem 2rem; color: #666;">
                <i class="fas fa-user-secret" style="font-size: 3rem; margin-bottom: 1rem; color: #ccc;"></i>
                <h3 style="margin-bottom: 0.5rem;">No conversations yet</h3>
                <p style="margin-bottom: 1.5rem;">Start a new conversation to begin baiting.</p>
                <a href="{{ route('scambaiter.create') }}" class="btn btn-primary">New Conversation</a>
            </div>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            @foreach($conversations as $conversation)
                <a href="{{ route('scambaiter.show', $conversation) }}" style="text-decoration: none; color: inherit;">
                    <div class="card scambaiter-card">
                        <div class="card-body">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <h3 style="margin: 0 0 0.25rem; font-size: 1rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $conversation->title }}
                                    </h3>
                                    @if($conversation->scammer_name)
                                        <p style="margin: 0 0 0.75rem; font-size: 0.8rem; color: #888;">
                                            <i class="fas fa-mask" style="margin-right: 4px;"></i>{{ $conversation->scammer_name }}
                                        </p>
                                    @endif
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                                        <span class="scambaiter-badge scambaiter-badge-intelligence">
                                            {{ $conversation->intelligence_level->label() }}
                                        </span>
                                        @if($conversation->is_locked)
                                            <span class="scambaiter-badge scambaiter-badge-locked">
                                                <i class="fas fa-lock" style="font-size: 0.65rem;"></i> Active
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">{{ $conversation->emails_count }}</div>
                                    <div style="font-size: 0.75rem; color: #888;">email{{ $conversation->emails_count === 1 ? '' : 's' }}</div>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.75rem; color: #888;">
                                Last updated {{ $conversation->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
