@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $year }} in Beeld</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>Jaar in Beeld</li>
                </ul>
            </div>
            <div>
                <select class="form-control" onchange="window.location.href='/music/report/year/' + this.value" style="width: auto;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($stats['overview']['total_plays'] === 0)
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <p style="color: #6b7280; font-size: 1.125rem;">Geen plays gevonden voor {{ $year }}.</p>
            </div>
        </div>
    @else

    {{-- Overview Stats --}}
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-play"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_plays']) }}</h3>
                        <p class="stat-label">Plays</p>
                        <span class="stat-change">{{ number_format($stats['overview']['unique_tracks']) }} unieke tracks</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_hours'], 1) }}u</h3>
                        <p class="stat-label">Luistertijd</p>
                        <span class="stat-change">{{ number_format($stats['overview']['total_days'], 1) }} dagen</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['unique_artists']) }}</h3>
                        <p class="stat-label">Artiesten</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['new_tracks']) }}</h3>
                        <p class="stat-label">Nieuwe ontdekkingen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Chart + Highlights --}}
    <div class="content-grid" style="margin-bottom: 2rem; grid-template-columns: 2fr 1fr;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i>Plays per maand</h3>
            </div>
            <div class="card-body" style="padding-bottom: 1.5rem;">
                @php
                    $maxPlays = max(array_column($stats['by_month'], 'play_count'));
                @endphp
                <div style="display: flex; align-items: flex-end; gap: 0.5rem; height: 160px; padding: 0 0.5rem;">
                    @foreach($stats['by_month'] as $month)
                        @php
                            $pct = $maxPlays > 0 ? round($month['play_count'] / $maxPlays * 100) : 0;
                        @endphp
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; height: 100%; justify-content: flex-end;">
                            <span style="font-size: 0.65rem; color: #6b7280;">{{ $month['play_count'] > 0 ? $month['play_count'] : '' }}</span>
                            <div style="width: 100%; background: linear-gradient(180deg, #667eea, #764ba2); border-radius: 4px 4px 0 0; height: {{ $pct }}%; min-height: {{ $month['play_count'] > 0 ? '4px' : '0' }}; transition: height 0.3s;"></div>
                            <span style="font-size: 0.7rem; color: #6b7280; white-space: nowrap;">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @if($stats['peak_hour'])
                <div class="card" style="flex: 1;">
                    <div class="card-body">
                        <p style="font-size: 0.75rem; color: #6b7280; margin: 0 0 0.25rem;">Piekuur</p>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ $stats['peak_hour']['label'] }}</div>
                        <div style="font-size: 0.85rem; color: #6b7280;">{{ number_format($stats['peak_hour']['play_count']) }} plays</div>
                    </div>
                </div>
            @endif

            @if($stats['most_active_day'])
                <div class="card" style="flex: 1;">
                    <div class="card-body">
                        <p style="font-size: 0.75rem; color: #6b7280; margin: 0 0 0.25rem;">Drukste dag</p>
                        <div style="font-size: 1.1rem; font-weight: 700; line-height: 1.3;">{{ $stats['most_active_day']['date'] }}</div>
                        <div style="font-size: 0.85rem; color: #6b7280;">{{ number_format($stats['most_active_day']['play_count']) }} plays</div>
                    </div>
                </div>
            @endif

            @if($stats['first_track'] || $stats['last_track'])
                <div class="card" style="flex: 1;">
                    <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @if($stats['first_track'])
                            <div>
                                <p style="font-size: 0.7rem; color: #6b7280; margin: 0 0 0.25rem; text-transform: uppercase; letter-spacing: 0.05em;">Eerste track</p>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    @if($stats['first_track']['album_image_url'])
                                        <img src="{{ $stats['first_track']['album_image_url'] }}" style="width: 32px; height: 32px; border-radius: 3px; flex-shrink: 0;">
                                    @endif
                                    <div style="min-width: 0;">
                                        @if($stats['first_track']['spotify_track_id'])
                                            <a href="{{ route('tracks.show', $stats['first_track']['spotify_track_id']) }}" style="font-weight: 600; font-size: 0.8rem; color: inherit; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $stats['first_track']['title'] }}</a>
                                        @else
                                            <div style="font-weight: 600; font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $stats['first_track']['title'] }}</div>
                                        @endif
                                        <div style="font-size: 0.7rem; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $stats['first_track']['played_at'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($stats['last_track'])
                            <div>
                                <p style="font-size: 0.7rem; color: #6b7280; margin: 0 0 0.25rem; text-transform: uppercase; letter-spacing: 0.05em;">Laatste track</p>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    @if($stats['last_track']['album_image_url'])
                                        <img src="{{ $stats['last_track']['album_image_url'] }}" style="width: 32px; height: 32px; border-radius: 3px; flex-shrink: 0;">
                                    @endif
                                    <div style="min-width: 0;">
                                        @if($stats['last_track']['spotify_track_id'])
                                            <a href="{{ route('tracks.show', $stats['last_track']['spotify_track_id']) }}" style="font-weight: 600; font-size: 0.8rem; color: inherit; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $stats['last_track']['title'] }}</a>
                                        @else
                                            <div style="font-weight: 600; font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $stats['last_track']['title'] }}</div>
                                        @endif
                                        <div style="font-size: 0.7rem; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $stats['last_track']['played_at'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Top Lists --}}
    <div class="content-grid" style="margin-bottom: 2rem;">
        {{-- Top Tracks --}}
        <div class="card">
            <div class="card-header">
                <h3>Top Tracks</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="top-list">
                    @foreach($stats['top_tracks'] as $index => $track)
                        <div class="top-list-item">
                            <div class="rank-number">{{ $index + 1 }}</div>
                            <div class="track-cover">
                                @if($track['album_image_url'])
                                    <img src="{{ $track['album_image_url'] }}" alt="{{ $track['title'] }}" class="cover-image">
                                @else
                                    <div class="cover-placeholder"><i class="fas fa-music"></i></div>
                                @endif
                            </div>
                            <div class="item-info">
                                <div class="item-name">
                                    @if($track['spotify_track_id'])
                                        <a href="{{ route('tracks.show', $track['spotify_track_id']) }}" class="item-link">{{ $track['title'] }}</a>
                                    @else
                                        <span>{{ $track['title'] }}</span>
                                    @endif
                                </div>
                                <div class="item-details">{{ $track['artist_name'] }}</div>
                            </div>
                            <div class="play-count">{{ $track['play_count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Artists --}}
        <div class="card">
            <div class="card-header">
                <h3>Top Artiesten</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="top-list">
                    @foreach($stats['top_artists'] as $index => $artist)
                        <div class="top-list-item">
                            <div class="rank-number">{{ $index + 1 }}</div>
                            <div class="track-cover">
                                @if($artist['image_url'])
                                    <img src="{{ $artist['image_url'] }}" alt="{{ $artist['name'] }}" class="cover-image" style="border-radius: 50%;">
                                @else
                                    <div class="cover-placeholder"><i class="fas fa-user"></i></div>
                                @endif
                            </div>
                            <div class="item-info">
                                <div class="item-name">
                                    <a href="{{ route('artists.show', ['artist' => urlencode($artist['name'])]) }}" class="item-link">{{ $artist['name'] }}</a>
                                </div>
                                <div class="item-details">
                                    {{ number_format($artist['unique_tracks']) }} tracks &bull;
                                    {{ round($artist['total_ms'] / 1000 / 3600, 1) }}u
                                </div>
                            </div>
                            <div class="play-count">{{ $artist['play_count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Albums --}}
        <div class="card">
            <div class="card-header">
                <h3>Top Albums</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="top-list">
                    @foreach($stats['top_albums'] as $index => $album)
                        <div class="top-list-item">
                            <div class="rank-number">{{ $index + 1 }}</div>
                            <div class="track-cover">
                                @if($album['image_url'])
                                    <img src="{{ $album['image_url'] }}" alt="{{ $album['album_name'] }}" class="cover-image">
                                @else
                                    <div class="cover-placeholder"><i class="fas fa-compact-disc"></i></div>
                                @endif
                            </div>
                            <div class="item-info">
                                <div class="item-name">{{ $album['album_name'] }}</div>
                                <div class="item-details">
                                    {{ $album['artist_name'] }} &bull; {{ $album['unique_tracks'] }} tracks
                                </div>
                            </div>
                            <div class="play-count">{{ $album['play_count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection
