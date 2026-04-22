@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Job Applications</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Job Applications</li>
                </ul>
            </div>
            <a href="{{ route('jobs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Job
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    <!-- Status summary -->
    <div class="stats-grid" style="margin-bottom: 1.5rem;">
        @foreach($statuses as $status)
            <div class="stat-card">
                <div class="stat-icon" style="background: {{ $status->color() }}20; color: {{ $status->color() }};">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $counts[$status->value] }}</div>
                    <div class="stat-label">{{ $status->label() }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Applications list -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            @forelse($applications as $job)
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
                            <span style="font-weight: 600; font-size: 0.95rem;">{{ $job->title }}</span>
                            <span style="font-size: 0.75rem; font-weight: 600; padding: 0.15rem 0.6rem; border-radius: 999px; background: {{ $job->status->color() }}20; color: {{ $job->status->color() }};">
                                {{ $job->status->label() }}
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; gap: 1rem; flex-wrap: wrap;">
                            <span><i class="fas fa-building" style="margin-right: 0.3rem;"></i>{{ $job->company }}</span>
                            @if($job->location)
                                <span><i class="fas fa-map-marker-alt" style="margin-right: 0.3rem;"></i>{{ $job->location }}</span>
                            @endif
                            @if($job->applied_at)
                                <span><i class="fas fa-calendar" style="margin-right: 0.3rem;"></i>Applied {{ $job->applied_at->format('d M Y') }}</span>
                            @endif
                        </div>
                        @if($job->notes)
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.35rem; white-space: pre-line;">{{ Str::limit($job->notes, 120) }}</div>
                        @endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                        @if($job->url)
                            <a href="{{ $job->url }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" title="Open listing">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                        <a href="{{ route('jobs.edit', $job) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <form method="POST" action="{{ route('jobs.destroy', $job) }}" onsubmit="return confirm('Remove this job application?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-briefcase" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    No job applications yet. <a href="{{ route('jobs.create') }}">Add your first one.</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
