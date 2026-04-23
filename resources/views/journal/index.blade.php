@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Journal</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Journal</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('journal.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i> Statistieken
                </a>
                <a href="{{ route('journal.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nieuwe entry
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-label">Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $streak }}</div>
                <div class="stat-label">Dag streak</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $tags->count() }}</div>
                <div class="stat-label">Tags</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-smile"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $moods->count() }}</div>
                <div class="stat-label">Moods beschikbaar</div>
            </div>
        </div>
    </div>

    <div class="journal-layout">
        <!-- Calendar & Filters -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Calendar -->
            <div class="card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <a href="{{ route('journal.index', array_merge(request()->except('month'), ['month' => $currentMonth->copy()->subMonth()->format('Y-m')])) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h3 style="margin: 0; font-size: 1rem;">{{ $currentMonth->locale('nl')->translatedFormat('F Y') }}</h3>
                        <a href="{{ route('journal.index', array_merge(request()->except('month'), ['month' => $currentMonth->copy()->addMonth()->format('Y-m')])) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <div class="health-calendar health-calendar-compact">
                        @foreach(['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'] as $day)
                            <div class="calendar-header">{{ $day }}</div>
                        @endforeach

                        @php
                            $startOfMonth = $currentMonth->copy()->startOfMonth();
                            $endOfMonth   = $currentMonth->copy()->endOfMonth();
                            $startDay     = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                            $endDay       = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                            $today        = now()->format('Y-m-d');
                        @endphp

                        @while($startDay <= $endDay)
                            @php
                                $dateKey        = $startDay->format('Y-m-d');
                                $entry          = $monthlyEntries[$dateKey] ?? null;
                                $isCurrentMonth = $startDay->month === $currentMonth->month;
                                $isToday        = $dateKey === $today;
                            @endphp
                            <div class="calendar-day {{ $entry ? 'has-entry' : '' }} {{ $isToday ? 'today' : '' }} {{ !$isCurrentMonth ? 'other-month' : '' }}"
                                 @if($entry) onclick="window.location='{{ route('journal.show', $entry) }}'" style="cursor: pointer;" @endif
                                 title="{{ $entry ? $entry->title : $startDay->format('d M') }}">
                                <span class="day-number">{{ $startDay->day }}</span>
                                @if($entry && $entry->rating)
                                    <span style="display: block; font-size: 0.55rem; font-weight: 700; color: {{ $entry->rating_color }}; line-height: 1;">{{ $entry->rating }}</span>
                                @endif
                            </div>
                            @php $startDay->addDay(); @endphp
                        @endwhile
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header"><h3>Filteren</h3></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('journal.index') }}">
                        <input type="hidden" name="month" value="{{ request('month') }}">

                        <div class="form-group">
                            <label>Zoeken</label>
                            <input type="text" name="search" class="form-control" placeholder="Titel of inhoud..." value="{{ request('search') }}">
                        </div>

                        <div class="form-group">
                            <label>Mood</label>
                            <select name="mood" class="form-control">
                                <option value="">Alle moods</option>
                                @foreach($moods as $mood)
                                    <option value="{{ $mood->id }}" {{ request('mood') == $mood->id ? 'selected' : '' }}>
                                        {{ $mood->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tag</label>
                            <select name="tag" class="form-control">
                                <option value="">Alle tags</option>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Minimaal cijfer</label>
                            <select name="rating_min" class="form-control">
                                <option value="">Geen minimum</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ request('rating_min') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-search"></i> Filteren
                            </button>
                            <a href="{{ route('journal.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Entry list -->
        <div>
            @if($entries->isEmpty())
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-book" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                        <h3 style="margin-bottom: 0.5rem;">Nog geen entries</h3>
                        <p style="color: #6b7280;">Start je journal met je eerste entry.</p>
                        <a href="{{ route('journal.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Eerste entry schrijven
                        </a>
                    </div>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($entries as $entry)
                        <a href="{{ route('journal.show', $entry) }}" class="journal-entry-card">
                            <div class="journal-entry-header">
                                <div>
                                    <div class="journal-entry-title">{{ $entry->title }}</div>
                                    <div class="journal-entry-meta">
                                        <span><i class="fas fa-calendar" style="margin-right: 0.3rem;"></i>{{ $entry->date->locale('nl')->translatedFormat('l d F Y') }}</span>
                                        @if($entry->location)
                                            <span><i class="fas fa-map-marker-alt" style="margin-right: 0.3rem;"></i>{{ $entry->location }}</span>
                                        @endif
                                        <span><i class="fas fa-file-word" style="margin-right: 0.3rem;"></i>{{ number_format($entry->word_count) }} woorden</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                                    @if($entry->mood)
                                        <span class="journal-mood-badge" style="background: {{ $entry->mood->color }}20; color: {{ $entry->mood->color }};">
                                            <i class="{{ $entry->mood->icon }}"></i> {{ $entry->mood->name }}
                                        </span>
                                    @endif
                                    @if($entry->rating)
                                        <span class="journal-rating-badge" style="background: {{ $entry->rating_color }}20; color: {{ $entry->rating_color }};">
                                            {{ $entry->rating }}/10
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($entry->excerpt)
                                <p class="journal-entry-excerpt">{{ $entry->excerpt }}</p>
                            @endif
                            @if($entry->tags->isNotEmpty())
                                <div class="journal-tags">
                                    @foreach($entry->tags as $tag)
                                        <span class="journal-tag" style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}40;">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div style="margin-top: 1.5rem;">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.journal-accent {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}

.journal-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .journal-layout {
        grid-template-columns: 1fr;
    }
}

.journal-entry-card {
    display: block;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.25rem;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.journal-entry-card:hover {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
}

.journal-entry-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.journal-entry-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.journal-entry-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.8rem;
    color: var(--text-muted);
}

.journal-entry-excerpt {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0.5rem 0;
    line-height: 1.5;
}

.journal-mood-badge,
.journal-rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.journal-tags {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-top: 0.75rem;
}

.journal-tag {
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 500;
    border: 1px solid;
}
</style>
@endsection
