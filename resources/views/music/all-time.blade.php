@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>All-Time Favorieten</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>All-Time Favorieten</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('music.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Music
                </a>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body" style="padding: 1rem 1.5rem;">
            <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">
                <i class="fas fa-info-circle" style="margin-right: 0.4rem;"></i>
                Deze data komt rechtstreeks van Spotify en is berekend over je volledige luistergeschiedenis — inclusief jaren vóór je begon met bijhouden in Aura.
            </p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

        <!-- Top Tracks -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-music" style="margin-right: 0.5rem;"></i>Top 50 Tracks</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Jouw meest gespeelde nummers ooit</p>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($topTracks->isEmpty())
                    <p class="no-data" style="padding: 1.5rem;">Geen data beschikbaar.</p>
                @else
                    <div class="all-time-list">
                        @foreach($topTracks as $index => $track)
                            <div class="all-time-item">
                                <span class="all-time-rank">{{ $index + 1 }}</span>
                                @if(!empty($track->album->images[2]->url ?? $track->album->images[0]->url ?? null))
                                    <img
                                        src="{{ $track->album->images[2]->url ?? $track->album->images[0]->url }}"
                                        alt="{{ $track->album->name }}"
                                        class="all-time-image"
                                    >
                                @else
                                    <div class="all-time-image all-time-image-placeholder">
                                        <i class="fas fa-music"></i>
                                    </div>
                                @endif
                                <div class="all-time-info">
                                    <a href="{{ route('tracks.show', $track->id) }}" class="all-time-name">
                                        {{ $track->name }}
                                    </a>
                                    <div class="all-time-sub">
                                        {{ collect($track->artists)->pluck('name')->implode(', ') }}
                                    </div>
                                </div>
                                <div class="all-time-popularity" title="Populariteit: {{ $track->popularity }}/100">
                                    <div class="popularity-bar">
                                        <div class="popularity-fill" style="width: {{ $track->popularity }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Artists -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-music" style="margin-right: 0.5rem;"></i>Top 50 Artiesten</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Jouw meest beluisterde artiesten ooit</p>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($topArtists->isEmpty())
                    <p class="no-data" style="padding: 1.5rem;">Geen data beschikbaar.</p>
                @else
                    <div class="all-time-list">
                        @foreach($topArtists as $index => $artist)
                            <div class="all-time-item">
                                <span class="all-time-rank">{{ $index + 1 }}</span>
                                @if(!empty($artist->images[2]->url ?? $artist->images[0]->url ?? null))
                                    <img
                                        src="{{ $artist->images[2]->url ?? $artist->images[0]->url }}"
                                        alt="{{ $artist->name }}"
                                        class="all-time-image all-time-image-round"
                                    >
                                @else
                                    <div class="all-time-image all-time-image-placeholder all-time-image-round">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div class="all-time-info">
                                    <a href="{{ route('artists.show', urlencode($artist->name)) }}" class="all-time-name">
                                        {{ $artist->name }}
                                    </a>
                                    @if(!empty($artist->genres))
                                        <div class="all-time-sub">
                                            {{ collect($artist->genres)->take(2)->implode(', ') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="all-time-popularity" title="Populariteit: {{ $artist->popularity }}/100">
                                    <div class="popularity-bar">
                                        <div class="popularity-fill" style="width: {{ $artist->popularity }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<style>
.all-time-list {
    display: flex;
    flex-direction: column;
}

.all-time-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 1.25rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    transition: background 0.15s;
}

.all-time-item:last-child {
    border-bottom: none;
}

.all-time-item:hover {
    background: var(--hover-bg, rgba(0,0,0,0.03));
}

.all-time-rank {
    min-width: 1.75rem;
    text-align: right;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-muted, #9ca3af);
}

.all-time-image {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.all-time-image-round {
    border-radius: 50%;
}

.all-time-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--card-bg-secondary, #f3f4f6);
    color: var(--text-muted, #9ca3af);
    font-size: 0.875rem;
}

.all-time-info {
    flex: 1;
    min-width: 0;
}

.all-time-name {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary, #111827);
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.all-time-name:hover {
    color: var(--accent-color, #6366f1);
}

.all-time-sub {
    font-size: 0.75rem;
    color: var(--text-muted, #9ca3af);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.all-time-popularity {
    width: 3.5rem;
    flex-shrink: 0;
}

.popularity-bar {
    height: 4px;
    background: var(--border-color, #e5e7eb);
    border-radius: 2px;
    overflow: hidden;
}

.popularity-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
}
</style>
@endsection
