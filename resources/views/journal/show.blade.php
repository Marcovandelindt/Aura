@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $journalEntry->title }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('journal.index') }}">Journal</a></li>
                    <li>{{ $journalEntry->title }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                @if($previousEntry)
                    <a href="{{ route('journal.show', $previousEntry) }}" class="btn btn-secondary btn-sm" title="Vorige entry">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif
                @if($nextEntry)
                    <a href="{{ route('journal.show', $nextEntry) }}" class="btn btn-secondary btn-sm" title="Volgende entry">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @endif
                <a href="{{ route('journal.edit', $journalEntry) }}" class="btn btn-secondary">
                    <i class="fas fa-pencil-alt"></i> Bewerken
                </a>
            </div>
        </div>
    </div>

    <div class="journal-show-layout">
        <!-- Main content -->
        <div>
            <div class="card">
                <div class="card-body">
                    <!-- Meta row -->
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color);">
                        <span style="font-size: 0.875rem; color: var(--text-muted);">
                            <i class="fas fa-calendar" style="margin-right: 0.35rem;"></i>
                            {{ $journalEntry->date->locale('nl')->translatedFormat('l d F Y') }}
                        </span>
                        @if($journalEntry->location)
                            <span style="font-size: 0.875rem; color: var(--text-muted);">
                                <i class="fas fa-map-marker-alt" style="margin-right: 0.35rem;"></i>
                                {{ $journalEntry->location }}
                            </span>
                        @endif
                        <span style="font-size: 0.875rem; color: var(--text-muted);">
                            <i class="fas fa-file-word" style="margin-right: 0.35rem;"></i>
                            {{ number_format($journalEntry->word_count) }} woorden
                        </span>
                        @if($journalEntry->mood)
                            <span class="journal-mood-badge" style="background: {{ $journalEntry->mood->color }}20; color: {{ $journalEntry->mood->color }};">
                                <i class="{{ $journalEntry->mood->icon }}"></i> {{ $journalEntry->mood->name }}
                            </span>
                        @endif
                        @if($journalEntry->rating)
                            <span class="journal-rating-badge" style="background: {{ $journalEntry->rating_color }}20; color: {{ $journalEntry->rating_color }};">
                                <i class="fas fa-star" style="font-size: 0.65rem;"></i> {{ $journalEntry->rating }}/10
                            </span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="journal-content">
                        {!! $journalEntry->content !!}
                    </div>

                    <!-- Tags -->
                    @if($journalEntry->tags->isNotEmpty())
                        <div class="journal-tags" style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color);">
                            @foreach($journalEntry->tags as $tag)
                                <span class="journal-tag" style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}40;">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Day rating visual -->
            @if($journalEntry->rating)
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Dagcijfer</div>
                        <div style="font-size: 3rem; font-weight: 800; color: {{ $journalEntry->rating_color }}; line-height: 1;">{{ $journalEntry->rating }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">/ 10</div>
                        <div style="margin-top: 0.75rem; height: 6px; background: var(--bg-secondary); border-radius: 999px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $journalEntry->rating * 10 }}%; background: {{ $journalEntry->rating_color }}; border-radius: 999px; transition: width 0.3s;"></div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Health data for this day -->
            @if($healthEntry)
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-heartbeat" style="margin-right: 0.4rem; color: #ef4444;"></i> Health deze dag</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            @if($healthEntry->steps)
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-shoe-prints"></i> Stappen</span>
                                    <span style="font-weight: 600; color: #10b981;">{{ $healthEntry->formatted_steps }}</span>
                                </div>
                            @endif
                            @if($healthEntry->avg_heart_rate)
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-heart"></i> Gem. hartslag</span>
                                    <span style="font-weight: 600; color: #ef4444;">{{ $healthEntry->formatted_avg_heart_rate }}</span>
                                </div>
                            @endif
                            @if($healthEntry->resting_heart_rate)
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-heartbeat"></i> Rustpols</span>
                                    <span style="font-weight: 600; color: #f59e0b;">{{ $healthEntry->formatted_resting_heart_rate }}</span>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('health.index') }}" style="display: block; margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted); text-align: right;">Naar health →</a>
                    </div>
                </div>
            @endif

            <!-- Strava activities for this day -->
            @if($stravaActivities->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-running" style="margin-right: 0.4rem; color: #f97316;"></i> Strava activiteiten</h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        @foreach($stravaActivities as $activity)
                            <a href="{{ route('strava.show', $activity) }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); text-decoration: none; color: inherit; transition: background 0.1s;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $activity->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $activity->type }}
                                        @if($activity->distance)
                                            · {{ number_format($activity->distance / 1000, 1) }} km
                                        @endif
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: var(--text-muted); font-size: 0.7rem;"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Entry navigation -->
            <div class="card">
                <div class="card-body" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @if($previousEntry)
                        <a href="{{ route('journal.show', $previousEntry) }}" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); text-decoration: none; padding: 0.4rem 0;">
                            <i class="fas fa-chevron-left" style="font-size: 0.7rem;"></i>
                            <div>
                                <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">Vorige</div>
                                <div style="color: var(--text-primary); font-weight: 500;">{{ Str::limit($previousEntry->title, 30) }}</div>
                            </div>
                        </a>
                    @endif
                    @if($nextEntry)
                        <a href="{{ route('journal.show', $nextEntry) }}" style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); text-decoration: none; padding: 0.4rem 0; text-align: right;">
                            <div>
                                <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">Volgende</div>
                                <div style="color: var(--text-primary); font-weight: 500;">{{ Str::limit($nextEntry->title, 30) }}</div>
                            </div>
                            <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.journal-show-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .journal-show-layout {
        grid-template-columns: 1fr;
    }
}

.journal-content {
    line-height: 1.75;
    font-size: 1rem;
}

.journal-content h2 { font-size: 1.4rem; font-weight: 700; margin: 1.5rem 0 0.5rem; }
.journal-content h3 { font-size: 1.15rem; font-weight: 600; margin: 1.25rem 0 0.4rem; }
.journal-content p { margin: 0.75rem 0; }
.journal-content ul, .journal-content ol { padding-left: 1.5rem; margin: 0.5rem 0; }
.journal-content li { margin: 0.25rem 0; }
.journal-content blockquote { border-left: 3px solid #6366f1; padding-left: 1rem; color: var(--text-muted); margin: 1rem 0; font-style: italic; }
.journal-content hr { border: none; border-top: 1px solid var(--border-color); margin: 1.5rem 0; }
.journal-content strong { font-weight: 700; }
.journal-content em { font-style: italic; }
.journal-content s { text-decoration: line-through; }

.journal-mood-badge,
.journal-rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
}

.journal-tags {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.journal-tag {
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    border: 1px solid;
}
</style>
@endsection
