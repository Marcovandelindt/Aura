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
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="white" class="spotify-icon">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                        </svg>
                        Data since {{ $stats['data_since']->setTimezone('Europe/Amsterdam')->format('M j, Y H:i') }}
                    </div>
                @endif
            </div>
            <div>
                <form action="{{ route('music.sync') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
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
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    Reconnect Spotify
                </a>
            @endif
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
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
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
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
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
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
                                    <a href="{{ route('tracks.show', ['track' => $track->spotify_track_id]) }}" 
                                       style="color: inherit; text-decoration: none; display: block;">
                                        <strong style="color: #333; transition: color 0.2s;">{{ $track->track_name }}</strong>
                                    </a>
                                    @if($track->preview_url)
                                        <a href="{{ $track->preview_url }}" target="_blank" 
                                           style="margin-left: 8px; color: #1DB954;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </a>
                                    @endif
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