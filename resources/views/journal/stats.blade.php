@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Journal statistieken</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('journal.index') }}">Journal</a></li>
                    <li>Statistieken</li>
                </ul>
            </div>
            <a href="{{ route('journal.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Terug
            </a>
        </div>
    </div>

    <!-- Summary stats -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($totalEntries) }}</div>
                <div class="stat-label">Entries geschreven</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $averageRating ? number_format($averageRating, 1) : '-' }}</div>
                <div class="stat-label">Gemiddeld cijfer</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-file-word"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($totalWords) }}</div>
                <div class="stat-label">Woorden totaal</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon journal-accent">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $streak }}</div>
                <div class="stat-label">Huidige streak</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Entries per maand -->
        <div class="card">
            <div class="card-header"><h3>Entries per maand</h3></div>
            <div class="card-body">
                @if($entriesByMonth->isEmpty())
                    <p style="color: var(--text-muted); text-align: center;">Nog geen data.</p>
                @else
                    @php $maxCount = $entriesByMonth->max('count'); @endphp
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach($entriesByMonth->take(-12)->reverse() as $row)
                            @php
                                $monthLabel = \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->locale('nl')->translatedFormat('M Y');
                                $pct = $maxCount > 0 ? ($row->count / $maxCount) * 100 : 0;
                            @endphp
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted); width: 60px; flex-shrink: 0;">{{ $monthLabel }}</span>
                                <div style="flex: 1; height: 8px; background: var(--bg-secondary); border-radius: 999px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $pct }}%; background: #6366f1; border-radius: 999px;"></div>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: 600; width: 20px; text-align: right;">{{ $row->count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Gemiddeld cijfer per maand -->
        <div class="card">
            <div class="card-header"><h3>Gemiddeld cijfer per maand</h3></div>
            <div class="card-body">
                @if($ratingsByMonth->isEmpty())
                    <p style="color: var(--text-muted); text-align: center;">Nog geen data.</p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach($ratingsByMonth->take(-12)->reverse() as $row)
                            @php
                                $monthLabel = \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->locale('nl')->translatedFormat('M Y');
                                $pct = ($row->avg_rating / 10) * 100;
                                $color = match(true) {
                                    $row->avg_rating >= 8 => '#10b981',
                                    $row->avg_rating >= 6 => '#f59e0b',
                                    $row->avg_rating >= 4 => '#f97316',
                                    default               => '#ef4444',
                                };
                            @endphp
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 0.8rem; color: var(--text-muted); width: 60px; flex-shrink: 0;">{{ $monthLabel }}</span>
                                <div style="flex: 1; height: 8px; background: var(--bg-secondary); border-radius: 999px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $color }}; border-radius: 999px;"></div>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: 600; width: 24px; text-align: right; color: {{ $color }};">{{ $row->avg_rating }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <!-- Mood verdeling -->
        <div class="card">
            <div class="card-header"><h3>Meest gebruikte moods</h3></div>
            <div class="card-body">
                @if($moodDistribution->isEmpty())
                    <p style="color: var(--text-muted); text-align: center;">Nog geen moods geregistreerd.</p>
                @else
                    @php $totalMoods = $moodDistribution->sum('count'); @endphp
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($moodDistribution as $row)
                            @php $pct = $totalMoods > 0 ? round(($row->count / $totalMoods) * 100) : 0; @endphp
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 1rem; width: 1.5rem; text-align: center;">{{ $row->mood?->icon }}</span>
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                                        <span style="font-size: 0.8rem; font-weight: 500;">{{ $row->mood?->name }}</span>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $row->count }}x ({{ $pct }}%)</span>
                                    </div>
                                    <div style="height: 6px; background: var(--bg-secondary); border-radius: 999px; overflow: hidden;">
                                        <div style="height: 100%; width: {{ $pct }}%; background: {{ $row->mood?->color ?? '#6366f1' }}; border-radius: 999px;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Top tags -->
        <div class="card">
            <div class="card-header"><h3>Meest gebruikte tags</h3></div>
            <div class="card-body">
                @if($topTags->isEmpty())
                    <p style="color: var(--text-muted); text-align: center;">Nog geen tags gebruikt.</p>
                @else
                    @php $maxTagCount = $topTags->max('entries_count'); @endphp
                    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                        @foreach($topTags as $tag)
                            @php $pct = $maxTagCount > 0 ? ($tag->entries_count / $maxTagCount) * 100 : 0; @endphp
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span class="journal-tag" style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}40; flex-shrink: 0;">{{ $tag->name }}</span>
                                <div style="flex: 1; height: 6px; background: var(--bg-secondary); border-radius: 999px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $tag->color }}; border-radius: 999px;"></div>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: 600; width: 20px; text-align: right;">{{ $tag->entries_count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.journal-accent {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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
