@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <h1>Op deze dag</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li>Op deze dag</li>
            </ul>
        </div>
        <form method="GET" action="{{ route('on-this-day.index') }}" class="otd-date-form">
            <input
                type="date"
                name="datum"
                value="{{ $referenceDate->toDateString() }}"
                max="{{ today()->toDateString() }}"
                class="form-control otd-date-input"
                onchange="this.form.submit()"
            >
            @if(!$referenceDate->isToday())
                <a href="{{ route('on-this-day.index') }}" class="btn btn-secondary btn-sm">Vandaag</a>
            @endif
        </form>
    </div>

    @if($memories->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 5rem 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🕰️</div>
                <h3 style="margin-bottom: 0.5rem;">Nog geen herinneringen</h3>
                <p style="color: var(--text-muted); max-width: 380px; margin: 0 auto;">
                    Geen data gevonden voor {{ $referenceDate->locale('nl')->isoFormat('D MMMM') }} in voorgaande jaren.
                </p>
            </div>
        </div>
    @else
        @foreach($memories as $memory)
            <div class="memory-section">

                {{-- Year header --}}
                <div class="memory-header">
                    <span class="memory-badge">{{ $memory['label'] }}</span>
                    <span class="memory-divider"></span>
                    <span class="memory-date">{{ $memory['date']->locale('nl')->isoFormat('dddd D MMMM YYYY') }}</span>
                </div>

                <div class="memory-grid">

                    {{-- ── Muziek ─────────────────────────────────────────── --}}
                    @if($memory['plays']->isNotEmpty())
                        <div class="memory-card" style="--accent: #6366f1;">
                            <div class="memory-card-header">
                                <i class="fas fa-music"></i>
                                <span>Muziek</span>
                                <span class="memory-card-meta">
                                    {{ $memory['plays']->count() }} plays
                                    @if($memory['uniqueTrackCount'] > 1)
                                        · {{ $memory['uniqueTrackCount'] }} nummers
                                    @endif
                                </span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['topTracks'] as $item)
                                    @if($item['track'])
                                        <div class="otd-track">
                                            <div class="otd-track-info">
                                                <div class="otd-track-title">{{ $item['track']->title }}</div>
                                                <div class="otd-track-artist">{{ $item['track']->artists_string ?: '—' }}</div>
                                            </div>
                                            <div class="otd-track-right">
                                                @if($item['count'] > 1)
                                                    <span class="otd-track-count">{{ $item['count'] }}×</span>
                                                @endif
                                                <span class="otd-time">{{ $item['last_played_at']->format('H:i') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @php $remaining = $memory['plays']->count() - $memory['topTracks']->count(); @endphp
                                @if($remaining > 0)
                                    <div class="otd-more">+ {{ $remaining }} meer nummers</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ── Sport ──────────────────────────────────────────── --}}
                    @if($memory['activities']->isNotEmpty())
                        @php
                            $sportIcons = [
                                'Run'           => 'fa-person-running',
                                'Ride'          => 'fa-bicycle',
                                'Swim'          => 'fa-person-swimming',
                                'Walk'          => 'fa-person-walking',
                                'Hike'          => 'fa-mountain',
                                'WeightTraining'=> 'fa-dumbbell',
                                'Workout'       => 'fa-dumbbell',
                                'Yoga'          => 'fa-spa',
                                'Soccer'        => 'fa-futbol',
                                'Tennis'        => 'fa-table-tennis-paddle-ball',
                                'Rowing'        => 'fa-ship',
                                'Kayaking'      => 'fa-ship',
                                'Skiing'        => 'fa-person-skiing',
                                'Snowboard'     => 'fa-person-snowboarding',
                            ];
                        @endphp
                        <div class="memory-card" style="--accent: #10b981;">
                            <div class="memory-card-header">
                                <i class="fas fa-person-running"></i>
                                <span>Sport</span>
                                <span class="memory-card-meta">{{ $memory['activities']->count() }} {{ $memory['activities']->count() === 1 ? 'activiteit' : 'activiteiten' }}</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['activities'] as $activity)
                                    <div class="otd-activity">
                                        <div class="otd-activity-icon">
                                            <i class="fas {{ $sportIcons[$activity->sport_type] ?? 'fa-person-running' }}"></i>
                                        </div>
                                        <div class="otd-activity-info">
                                            <div class="otd-activity-name">{{ $activity->name }}</div>
                                            <div class="otd-activity-stats">
                                                @if($activity->distance > 0)
                                                    <span><i class="fas fa-route"></i> {{ $activity->distance_in_km }} km</span>
                                                @endif
                                                @if($activity->moving_time)
                                                    <span><i class="fas fa-clock"></i> {{ $activity->moving_time_pretty }}</span>
                                                @endif
                                                @if($activity->average_heartrate)
                                                    <span><i class="fas fa-heart"></i> {{ round($activity->average_heartrate) }} bpm</span>
                                                @endif
                                                @if($activity->calories)
                                                    <span><i class="fas fa-fire"></i> {{ round($activity->calories) }} kcal</span>
                                                @endif
                                                @if($activity->weather_temp !== null)
                                                    <span><i class="fas fa-cloud-sun"></i> {{ round($activity->weather_temp) }}°C</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── Gezondheid ─────────────────────────────────────── --}}
                    @if($memory['health'])
                        @php $health = $memory['health']; $stepGoal = $health::getStepGoal(); @endphp
                        <div class="memory-card" style="--accent: #f43f5e;">
                            <div class="memory-card-header">
                                <i class="fas fa-heartbeat"></i>
                                <span>Gezondheid</span>
                            </div>
                            <div class="memory-card-body">
                                @if($health->steps)
                                    <div class="otd-health-row">
                                        <span class="otd-health-label">Stappen</span>
                                        <span class="otd-health-value {{ $health->steps >= $stepGoal ? 'otd-health-value--good' : '' }}">
                                            {{ number_format($health->steps, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="otd-steps-track">
                                        <div class="otd-steps-fill" style="width: {{ min(100, round($health->steps / $stepGoal * 100)) }}%; background: {{ $health->steps >= $stepGoal ? '#10b981' : '#f43f5e' }};"></div>
                                    </div>
                                    <div class="otd-steps-goal">doel: {{ number_format($stepGoal, 0, ',', '.') }}</div>
                                @endif
                                @if($health->resting_heart_rate)
                                    <div class="otd-health-row" style="margin-top: 0.75rem;">
                                        <span class="otd-health-label">Rusthartslag</span>
                                        <span class="otd-health-value">{{ $health->resting_heart_rate }} bpm</span>
                                    </div>
                                @endif
                                @if($health->avg_heart_rate)
                                    <div class="otd-health-row">
                                        <span class="otd-health-label">Gem. hartslag</span>
                                        <span class="otd-health-value">{{ $health->avg_heart_rate }} bpm</span>
                                    </div>
                                @endif
                                @if($health->notes)
                                    <div class="otd-journal-excerpt" style="margin-top: 0.5rem;">{{ $health->notes }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ── Gaming ─────────────────────────────────────────── --}}
                    @if($memory['psSessions']->isNotEmpty() || $memory['nsSessions']->isNotEmpty())
                        @php
                            $totalGamingMinutes = $memory['psSessions']->sum('duration_minutes')
                                + $memory['nsSessions']->sum('duration_minutes');
                            $gamingHours = floor($totalGamingMinutes / 60);
                            $gamingMins  = $totalGamingMinutes % 60;
                            $totalLabel  = $gamingHours > 0 ? "{$gamingHours}h {$gamingMins}m" : "{$gamingMins}m";
                        @endphp
                        <div class="memory-card" style="--accent: #8b5cf6;">
                            <div class="memory-card-header">
                                <i class="fas fa-gamepad"></i>
                                <span>Gaming</span>
                                <span class="memory-card-meta">{{ $totalLabel }} totaal</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['psSessions'] as $session)
                                    <div class="otd-game">
                                        <span class="otd-game-platform otd-game-platform--ps">PS</span>
                                        <span class="otd-game-name">{{ $session->game?->name ?? '—' }}</span>
                                        <span class="otd-time">{{ $session->started_at->format('H:i') }}</span>
                                        <span class="otd-game-duration">{{ $session->formatted_duration }}</span>
                                    </div>
                                @endforeach
                                @foreach($memory['nsSessions'] as $session)
                                    <div class="otd-game">
                                        <span class="otd-game-platform otd-game-platform--ns">NS</span>
                                        <span class="otd-game-name">{{ $session->game?->name ?? '—' }}</span>
                                        <span class="otd-game-duration">{{ $session->formatted_duration }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── Journal ────────────────────────────────────────── --}}
                    @if($memory['journalEntries']->isNotEmpty())
                        <div class="memory-card" style="--accent: #f59e0b;">
                            <div class="memory-card-header">
                                <i class="fas fa-book"></i>
                                <span>Journal</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['journalEntries'] as $entry)
                                    <div class="otd-journal{{ !$loop->first ? ' otd-journal--sep' : '' }}">
                                        <div class="otd-journal-top">
                                            @if($entry->title)
                                                <div class="otd-journal-title">{{ $entry->title }}</div>
                                            @endif
                                            <div class="otd-journal-badges">
                                                @if($entry->rating)
                                                    <span class="otd-rating" style="background: {{ $entry->rating_color }}20; color: {{ $entry->rating_color }};">
                                                        {{ $entry->rating }}/10
                                                    </span>
                                                @endif
                                                @if($entry->mood)
                                                    <span class="otd-mood">{{ $entry->mood->icon }} {{ $entry->mood->name }}</span>
                                                @endif
                                                @if($entry->location)
                                                    <span class="otd-mood"><i class="fas fa-location-dot" style="font-size: 0.6rem;"></i> {{ $entry->location }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($entry->excerpt)
                                            <div class="otd-journal-excerpt">{{ $entry->excerpt }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── Film & TV ───────────────────────────────────────── --}}
                    @if($memory['movieWatches']->isNotEmpty() || $memory['episodeWatches']->isNotEmpty())
                        @php
                            $episodesBySeries = $memory['episodeWatches']
                                ->groupBy(fn ($w) => $w->episode?->season?->series?->name ?? 'Onbekend');
                        @endphp
                        <div class="memory-card" style="--accent: #3b82f6;">
                            <div class="memory-card-header">
                                <i class="fas fa-film"></i>
                                <span>Film & TV</span>
                                @php
                                    $mediaCount = $memory['movieWatches']->count() + $memory['episodeWatches']->count();
                                @endphp
                                <span class="memory-card-meta">{{ $mediaCount }} {{ $mediaCount === 1 ? 'item' : 'items' }}</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['movieWatches'] as $watch)
                                    <div class="otd-media">
                                        <span class="otd-media-type"><i class="fas fa-film"></i></span>
                                        <div class="otd-media-info">
                                            <div class="otd-media-title">{{ $watch->movie?->title ?? '—' }}</div>
                                        </div>
                                        @if($watch->rating)
                                            <span class="otd-media-rating">★ {{ $watch->rating }}</span>
                                        @endif
                                    </div>
                                @endforeach

                                @foreach($episodesBySeries as $seriesName => $watches)
                                    <div class="otd-series">
                                        <div class="otd-series-name">
                                            <i class="fas fa-tv" style="font-size: 0.7rem; margin-right: 0.3rem; color: #3b82f6;"></i>
                                            {{ $seriesName }}
                                        </div>
                                        @foreach($watches as $watch)
                                            @if($watch->episode)
                                                <div class="otd-episode">
                                                    S{{ str_pad($watch->episode->season?->season_number ?? 0, 2, '0', STR_PAD_LEFT) }}E{{ str_pad($watch->episode->episode_number, 2, '0', STR_PAD_LEFT) }}
                                                    · {{ $watch->episode->name }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── Afgeronde taken ────────────────────────────────── --}}
                    @if($memory['completedTasks']->isNotEmpty())
                        <div class="memory-card" style="--accent: #14b8a6;">
                            <div class="memory-card-header">
                                <i class="fas fa-circle-check"></i>
                                <span>Afgeronde taken</span>
                                <span class="memory-card-meta">{{ $memory['completedTasks']->count() }}</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['completedTasks']->take(7) as $task)
                                    <div class="otd-task">
                                        <i class="fas fa-check otd-task-check"></i>
                                        <span>{{ $task->title }}</span>
                                        <span class="task-badge difficulty-badge-{{ $task->difficulty }}" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">{{ $task->difficultyLabel() }}</span>
                                    </div>
                                @endforeach
                                @if($memory['completedTasks']->count() > 7)
                                    <div class="otd-more">+ {{ $memory['completedTasks']->count() - 7 }} meer taken</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ── Uitgaven ────────────────────────────────────────── --}}
                    @if($memory['expenses']->isNotEmpty())
                        <div class="memory-card" style="--accent: #f97316;">
                            <div class="memory-card-header">
                                <i class="fas fa-euro-sign"></i>
                                <span>Uitgaven</span>
                                <span class="memory-card-meta">€ {{ number_format($memory['expenses']->sum('amount'), 2, ',', '.') }} totaal</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['expenses'] as $expense)
                                    <div class="otd-expense">
                                        <div class="otd-expense-left">
                                            @if($expense->expenseCategory)
                                                <span class="otd-expense-category" style="color: {{ $expense->expenseCategory->color ?? 'var(--text-muted)' }};">
                                                    {{ $expense->expenseCategory->icon ?? '' }} {{ $expense->expenseCategory->name }}
                                                </span>
                                            @endif
                                            <span class="otd-expense-desc">{{ $expense->description }}</span>
                                        </div>
                                        <span class="otd-expense-amount">€ {{ number_format($expense->amount, 2, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── Achievements ───────────────────────────────────── --}}
                    @if($memory['achievements']->isNotEmpty())
                        <div class="memory-card" style="--accent: #eab308;">
                            <div class="memory-card-header">
                                <i class="fas fa-trophy"></i>
                                <span>Achievements</span>
                            </div>
                            <div class="memory-card-body">
                                @foreach($memory['achievements'] as $unlocked)
                                    <div class="otd-achievement">
                                        <span class="otd-achievement-icon">{{ $unlocked->achievement?->icon }}</span>
                                        <div>
                                            <div class="otd-achievement-name">{{ $unlocked->achievement?->name }}</div>
                                            <div class="otd-achievement-desc">{{ $unlocked->achievement?->description }}</div>
                                        </div>
                                        <span class="otd-achievement-xp">+{{ $unlocked->achievement?->xp_bonus }} XP</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>{{-- /memory-grid --}}
            </div>{{-- /memory-section --}}
        @endforeach
    @endif
</div>

<style>
/* Date picker in header */
.otd-date-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.otd-date-input {
    width: auto;
    padding: 0.35rem 0.65rem;
    font-size: 0.85rem;
    height: auto;
}

/* Year section */
.memory-section {
    margin-bottom: 3rem;
}

/* Year header */
.memory-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.memory-badge {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.3rem 0.9rem;
    border-radius: 999px;
    white-space: nowrap;
    letter-spacing: 0.02em;
}

.memory-divider {
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

.memory-date {
    font-size: 0.85rem;
    color: var(--text-muted);
    white-space: nowrap;
    font-weight: 500;
    text-transform: capitalize;
}

/* Cards grid */
.memory-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    align-items: start;
}

/* Individual card */
.memory-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-top: 3px solid var(--accent, var(--border-color));
    border-radius: 0.75rem;
    overflow: hidden;
}

.memory-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color);
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--accent, var(--text-color));
}

.memory-card-header i {
    width: 1rem;
    text-align: center;
    flex-shrink: 0;
}

.memory-card-meta {
    margin-left: auto;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
}

.memory-card-body {
    padding: 0.75rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* ── Music ── */
.otd-track {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.otd-track-info {
    flex: 1;
    min-width: 0;
}

.otd-track-title {
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.otd-track-artist {
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.otd-track-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.1rem;
    flex-shrink: 0;
}

.otd-track-count {
    font-size: 0.75rem;
    font-weight: 700;
    color: #6366f1;
}

.otd-time {
    font-size: 0.7rem;
    color: var(--text-muted);
    font-variant-numeric: tabular-nums;
}

/* ── Activities ── */
.otd-activity {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.otd-activity-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: #10b98115;
    color: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.otd-activity-info {
    flex: 1;
    min-width: 0;
}

.otd-activity-name {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}

.otd-activity-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.72rem;
    color: var(--text-muted);
}

.otd-activity-stats span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* ── Health ── */
.otd-health-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
}

.otd-health-label {
    color: var(--text-muted);
}

.otd-health-value {
    font-weight: 700;
}

.otd-health-value--good {
    color: #10b981;
}

.otd-steps-track {
    height: 5px;
    background: var(--border-color);
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.3rem;
}

.otd-steps-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.6s ease;
}

.otd-steps-goal {
    font-size: 0.68rem;
    color: var(--text-muted);
    margin-top: 0.2rem;
    text-align: right;
}

/* ── Gaming ── */
.otd-game {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.85rem;
}

.otd-game-platform {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 0.15rem 0.35rem;
    border-radius: 0.25rem;
    flex-shrink: 0;
    letter-spacing: 0.03em;
}

.otd-game-platform--ps {
    background: #003087;
    color: white;
}

.otd-game-platform--ns {
    background: #e4000f;
    color: white;
}

.otd-game-name {
    flex: 1;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.otd-game-duration {
    font-size: 0.75rem;
    color: var(--text-muted);
    flex-shrink: 0;
}

/* ── Journal ── */
.otd-journal {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.otd-journal--sep {
    padding-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    margin-top: 0.25rem;
}

.otd-journal-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.otd-journal-title {
    font-size: 0.85rem;
    font-weight: 700;
}

.otd-journal-badges {
    display: flex;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.otd-rating {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
}

.otd-mood {
    font-size: 0.72rem;
    color: var(--text-muted);
    background: var(--border-color);
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
}

.otd-journal-excerpt {
    font-size: 0.8rem;
    color: var(--text-muted);
    line-height: 1.5;
}

/* ── Media ── */
.otd-media {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.85rem;
}

.otd-media-type {
    color: #3b82f6;
    width: 1rem;
    text-align: center;
    flex-shrink: 0;
    font-size: 0.75rem;
}

.otd-media-info {
    flex: 1;
    min-width: 0;
}

.otd-media-title {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.otd-media-rating {
    font-size: 0.75rem;
    color: #f59e0b;
    font-weight: 700;
    flex-shrink: 0;
}

.otd-series {
    border-top: 1px solid var(--border-color);
    padding-top: 0.4rem;
    margin-top: 0.1rem;
}

.otd-series:first-child {
    border-top: none;
    padding-top: 0;
}

.otd-series-name {
    font-size: 0.82rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}

.otd-episode {
    font-size: 0.76rem;
    color: var(--text-muted);
    padding-left: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Tasks ── */
.otd-task {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
}

.otd-task-check {
    color: #14b8a6;
    font-size: 0.65rem;
    flex-shrink: 0;
}

/* ── Expenses ── */
.otd-expense {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    font-size: 0.82rem;
}

.otd-expense-left {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    min-width: 0;
}

.otd-expense-category {
    font-size: 0.7rem;
    font-weight: 600;
}

.otd-expense-desc {
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.otd-expense-amount {
    font-weight: 700;
    flex-shrink: 0;
    color: #f97316;
}

/* ── Achievements ── */
.otd-achievement {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.otd-achievement-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.otd-achievement-name {
    font-size: 0.85rem;
    font-weight: 700;
}

.otd-achievement-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.otd-achievement-xp {
    margin-left: auto;
    font-size: 0.78rem;
    font-weight: 700;
    color: #f59e0b;
    flex-shrink: 0;
}

/* Shared */
.otd-more {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

@media (max-width: 640px) {
    .memory-grid {
        grid-template-columns: 1fr;
    }

    .memory-header {
        flex-wrap: wrap;
    }

    .memory-divider {
        display: none;
    }
}
</style>
@endsection
