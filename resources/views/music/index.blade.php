@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Music History</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Music</li>
                </ul>
                @if($stats['data_since'])
                    <div class="data-since-badge">
                        <i class="fab fa-spotify spotify-icon"></i>
                        Spotify since {{ $stats['data_since']->setTimezone('Europe/Amsterdam')->format('M j, Y H:i') }}
                    </div>
                @endif
            </div>
            <div>
                <form action="{{ route('music.sync') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>
                        Sync Now
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
            @if(str_contains(session('error'), 'authentication expired') || str_contains(session('error'), 'reconnect'))
                <br><br>
                <a href="{{ route('spotify.auth') }}" class="btn btn-primary" style="margin-top: 0.5rem;">
                    <i class="fab fa-spotify" style="margin-right: 8px;"></i>
                    Reconnect Spotify
                </a>
            @endif
        </div>
    @endif

    <!-- Top Artist This Week -->
    @if($topArtistThisWeek)
        <div class="top-artist-widget" style="margin-bottom: 2rem;">
            <div class="top-artist-content">
                <div class="top-artist-header">
                    <div class="top-artist-badge">
                        <i class="fas fa-star"></i>
                        Top Artist This Week
                    </div>
                </div>
                
                <div class="top-artist-main">
                    @if($topArtistThisWeek->album_image_url)
                        <div class="top-artist-image-container">
                            <img src="{{ $topArtistThisWeek->album_image_url }}" 
                                 alt="{{ $topArtistThisWeek->name }}"
                                 class="top-artist-image">
                            <div class="top-artist-overlay">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    @endif
                    
                    <div class="top-artist-info">
                        <h3 class="top-artist-name">
                            <a href="{{ route('artists.show', ['artist' => urlencode($topArtistThisWeek->name)]) }}">
                                {{ $topArtistThisWeek->name }}
                            </a>
                        </h3>
                        
                        <div class="top-artist-stats-grid">
                            <div class="top-artist-stat">
                                <span class="stat-number">{{ $topArtistThisWeek->plays }}</span>
                                <span class="stat-label">plays</span>
                            </div>
                            <div class="top-artist-stat">
                                <span class="stat-number">{{ $topArtistThisWeek->unique_tracks_count }}</span>
                                <span class="stat-label">tracks</span>
                            </div>
                            <div class="top-artist-stat">
                                @php
                                    $totalMinutes = floor($topArtistThisWeek->total_duration_ms / 1000 / 60);
                                @endphp
                                <span class="stat-number">{{ $totalMinutes }}m</span>
                                <span class="stat-label">listening time</span>
                            </div>
                            @if($topArtistThisWeek->streak_days > 0)
                                <div class="top-artist-stat">
                                    <span class="stat-number">{{ $topArtistThisWeek->streak_days }}</span>
                                    <span class="stat-label">day{{ $topArtistThisWeek->streak_days > 1 ? 's' : '' }} streak</span>
                                </div>
                            @endif
                            @if($topArtistThisWeek->peak_hour)
                                <div class="top-artist-stat">
                                    <span class="stat-number">{{ $topArtistThisWeek->peak_hour }}</span>
                                    <span class="stat-label">peak hour ({{ $topArtistThisWeek->peak_hour_plays }} plays)</span>
                                </div>
                            @endif
                            @if($topArtistThisWeek->top_day)
                                <div class="top-artist-stat top-day-stat">
                                    <span class="stat-number">{{ $topArtistThisWeek->top_day }}</span>
                                    <span class="stat-label">top day ({{ $topArtistThisWeek->top_day_plays }} plays)</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="top-artist-tracks">
                            <p class="tracks-label">Most played tracks:</p>
                            <div class="tracks-list">
                                @foreach(array_slice($topArtistThisWeek->unique_tracks, 0, 3) as $trackName)
                                    <span class="track-pill">{{ $trackName }}</span>
                                @endforeach
                                @if(count($topArtistThisWeek->unique_tracks) > 3)
                                    <span class="track-pill more">+{{ count($topArtistThisWeek->unique_tracks) - 3 }} more</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['total_tracks']) }}</h3>
                        <p class="stat-label">Total Plays</p>
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
                        <h3 class="stat-value">{{ number_format($stats['unique_tracks']) }}</h3>
                        <p class="stat-label">Unique Tracks</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">
                            @php
                                $totalMinutes = floor($stats['total_duration_ms'] / 1000 / 60);
                                $hours = floor($totalMinutes / 60);
                                $minutes = $totalMinutes % 60;
                            @endphp
                            {{ $hours }}h {{ $minutes }}m
                        </h3>
                        <p class="stat-label">Total Listening Time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('music.index') }}" style="display: flex; gap: 1rem; align-items: center;">
                <div style="flex: 1;">
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="Search tracks, artists, albums..." 
                           class="form-control"
                           style="width: 100%;">
                </div>
                <div>
                    <select name="filter" class="form-control" onchange="this.form.submit()">
                        <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $filter == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search || $filter != 'all')
                    <a href="{{ route('music.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Tracks List -->
    <div class="card">
        <div class="card-body">
            @if($tracks->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50"></th>
                                <th>Track</th>
                                <th>Artist</th>
                                <th>Album</th>
                                <th>Duration</th>
                                <th>Played At</th>
                                <th width="50">Mood</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tracks as $track)
                            <tr>
                                <td>
                                    @if($track->album_image_url)
                                        <img src="{{ $track->album_image_url }}" 
                                             alt="{{ $track->album_name }}"
                                             style="width: 40px; height: 40px; border-radius: 4px;">
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <i class="fab fa-spotify" style="color: #1DB954; font-size: 0.75rem; flex-shrink: 0;" title="Spotify"></i>
                                        @if($track->spotify_track_id)
                                            <a href="{{ route('tracks.show', ['track' => $track->spotify_track_id]) }}"
                                               style="color: inherit; text-decoration: none;">
                                                <strong style="color: #333; transition: color 0.2s;">{{ $track->track_name }}</strong>
                                            </a>
                                        @else
                                            <strong style="color: #333;">{{ $track->track_name }}</strong>
                                        @endif
                                        @if($track->preview_url)
                                            <a href="{{ $track->preview_url }}" target="_blank"
                                               style="color: #1DB954;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                    <x-mood-pills :moods="$track->moods ?? []" :compact="true" />
                                </td>
                                <td>
                                    @foreach($track->artist_names as $index => $artistName)
                                        <a href="{{ route('artists.show', ['artist' => urlencode($artistName)]) }}" 
                                           style="color: #666; text-decoration: none; transition: color 0.2s;"
                                           onmouseover="this.style.color='#1DB954'" 
                                           onmouseout="this.style.color='#666'">{{ $artistName }}</a>@if($index < count($track->artist_names) - 1), @endif
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('albums.show', ['album' => urlencode($track->album_name)]) }}" 
                                       style="color: #666; text-decoration: none; transition: color 0.2s;"
                                       onmouseover="this.style.color='#1DB954'" 
                                       onmouseout="this.style.color='#666'">{{ $track->album_name }}</a>
                                </td>
                                <td>{{ $track->formatted_duration }}</td>
                                <td>
                                    <span title="{{ $track->played_at->setTimezone('Europe/Amsterdam')->format('Y-m-d H:i:s') }}">
                                        {{ $track->played_at->setTimezone('Europe/Amsterdam')->format('M j, H:i') }}
                                    </span>
                                </td>
                                <td>
                                    @if($track->spotify_track_id)
                                        <button class="mood-trigger{{ !empty($track->moods) ? ' has-moods' : '' }}"
                                                data-track-id="{{ $track->spotify_track_id }}"
                                                onclick="openMoodPopup('{{ $track->spotify_track_id }}', '{{ addslashes($track->track_name) }}', '{{ addslashes(implode(', ', $track->artist_names)) }}')"
                                                title="{{ !empty($track->moods) ? 'Manage moods' : 'Add mood to track' }}">
                                            <i class="fas fa-{{ !empty($track->moods) ? 'tags' : 'plus' }}"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $tracks->links() }}
            @else
                <p class="text-center" style="padding: 2rem;">
                    No tracks found. Try syncing your Spotify history.
                </p>
            @endif
        </div>
    </div>
</div>

@endsection