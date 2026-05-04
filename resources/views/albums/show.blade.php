@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <a href="{{ route('music.index') }}" class="back-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                        </svg>
                        Back to Music
                    </a>
                </div>
                <h1>{{ $albumName }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>{{ $albumName }}</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Album Info Header -->
    @if($topTrack)
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <div class="album-header">
                    @if($topTrack->album_image_url)
                        <img src="{{ $topTrack->album_image_url }}" 
                             alt="{{ $albumName }}"
                             class="album-image">
                    @endif
                    
                    <div class="album-info">
                        <div class="album-type-label">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <span>Album</span>
                        </div>
                        
                        <h2 class="album-name">{{ $albumName }}</h2>
                        
                        <div class="album-artists">
                            <strong>by </strong>
                            @foreach($albumArtists as $index => $artistName)
                                <a href="{{ route('artists.show', ['artist' => urlencode($artistName)]) }}" 
                                   style="color: #1DB954; text-decoration: none; font-weight: 600;">{{ $artistName }}</a>@if($index < count($albumArtists) - 1), @endif
                            @endforeach
                        </div>
                        
                        <div class="album-stats-inline">
                            <div class="album-stat-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                                <span>{{ $stats['unique_tracks'] }} tracks</span>
                            </div>
                            
                            <div class="album-stat-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                                @php
                                    $totalMinutes = floor($stats['total_duration_ms'] / 1000 / 60);
                                    $hours = floor($totalMinutes / 60);
                                    $minutes = $totalMinutes % 60;
                                @endphp
                                <span>{{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes }}m total duration</span>
                            </div>
                        </div>
                        
                        <div class="album-top-track">
                            <p class="top-track-label"><strong>Most played track:</strong></p>
                            <p class="top-track-name">
                                @if($topTrack->spotify_track_id)
                                    <a href="{{ route('tracks.show', ['track' => $topTrack->spotify_track_id]) }}"
                                       style="color: inherit; text-decoration: none;">
                                        {{ $topTrack->track_name }}
                                    </a>
                                @else
                                    {{ $topTrack->track_name }}
                                @endif
                                ({{ $topTrack->play_count }} plays)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Stats Grid -->
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
                        <h3 class="stat-value">{{ $stats['total_plays'] }}</h3>
                        <p class="stat-label">Total Plays</p>
                        <span class="stat-change">All time</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['plays_this_week'] }}</h3>
                        <p class="stat-label">This Week</p>
                        <span class="stat-change">{{ $stats['plays_this_month'] }} this month</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['first_played'] ? $stats['first_played']->setTimezone('Europe/Amsterdam')->format('M j') : 'N/A' }}</h3>
                        <p class="stat-label">First Played</p>
                        <span class="stat-change">{{ $stats['first_played'] ? $stats['first_played']->setTimezone('Europe/Amsterdam')->format('H:i') . ' - ' . $stats['first_played']->diffForHumans() : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['last_played'] ? $stats['last_played']->setTimezone('Europe/Amsterdam')->format('M j') : 'N/A' }}</h3>
                        <p class="stat-label">Last Played</p>
                        <span class="stat-change">{{ $stats['last_played'] ? $stats['last_played']->setTimezone('Europe/Amsterdam')->format('H:i') . ' - ' . $stats['last_played']->diffForHumans() : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Album Tracks -->
        <div class="card">
            <div class="card-header">
                <h3>Tracks from {{ $albumName }}</h3>
            </div>
            <div class="card-body">
                @if($albumTracks->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="50"></th>
                                    <th>Track</th>
                                    <th>Artist(s)</th>
                                    <th>Plays</th>
                                    <th>Last Played</th>
                                    <th width="50">Mood</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($albumTracks as $track)
                                <tr>
                                    <td>
                                        @if($track->album_image_url)
                                            <img src="{{ $track->album_image_url }}" 
                                                 alt="{{ $track->album_name }}"
                                                 style="width: 40px; height: 40px; border-radius: 4px;">
                                        @endif
                                    </td>
                                    <td>
                                        @if($track->spotify_track_id)
                                            <a href="{{ route('tracks.show', ['track' => $track->spotify_track_id]) }}"
                                               style="color: inherit; text-decoration: none; display: block;">
                                                <strong style="color: #333; transition: color 0.2s;">{{ $track->track_name }}</strong>
                                            </a>
                                        @else
                                            <strong style="color: #333;">{{ $track->track_name }}</strong>
                                        @endif
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
                                        <span style="background: linear-gradient(135deg, #1DB954 0%, #1ed760 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                                            {{ $track->play_count }} plays
                                        </span>
                                    </td>
                                    <td style="color: #666;">{{ $track->last_played ? \Carbon\Carbon::parse($track->last_played)->setTimezone('Europe/Amsterdam')->format('M j, H:i') : 'N/A' }}</td>
                                    <td>
                                        <button class="mood-trigger{{ !empty($track->moods) ? ' has-moods' : '' }}" 
                                                data-track-id="{{ $track->spotify_track_id }}"
                                                onclick="openMoodPopup('{{ $track->spotify_track_id }}', '{{ addslashes($track->track_name) }}', '{{ addslashes(implode(', ', $track->artist_names)) }}')"
                                                title="{{ !empty($track->moods) ? 'Manage moods' : 'Add mood to track' }}">
                                            <i class="fas fa-{{ !empty($track->moods) ? 'tags' : 'plus' }}"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for tracks -->
                    @if($albumTracks->hasPages())
                        <div style="margin-top: 1rem;">
                            {{ $albumTracks->links() }}
                        </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 1rem; color: #ccc;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p>No tracks found for this album.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Plays -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Plays from Album</h3>
            </div>
            <div class="card-body">
                @if($allPlays->count() > 0)
                    <div class="activity-list">
                        @foreach($allPlays->take(10) as $play)
                            <div class="activity-item">
                                <div class="activity-icon">
                                    @if($play->album_image_url)
                                        <img src="{{ $play->album_image_url }}" 
                                             alt="{{ $play->album_name }}"
                                             style="width: 40px; height: 40px; border-radius: 6px;">
                                    @else
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="activity-content">
                                    <p>
                                        @if($play->spotify_track_id)
                                            <a href="{{ route('tracks.show', ['track' => $play->spotify_track_id]) }}"
                                               style="color: inherit; text-decoration: none;">
                                                <strong>{{ $play->track_name }}</strong>
                                            </a>
                                        @else
                                            <strong>{{ $play->track_name }}</strong>
                                        @endif
                                        by 
                                        @foreach($play->artist_names as $index => $artistName)
                                            <a href="{{ route('artists.show', ['artist' => urlencode($artistName)]) }}" 
                                               style="color: #1DB954; text-decoration: none;">{{ $artistName }}</a>@if($index < count($play->artist_names) - 1), @endif
                                        @endforeach
                                    </p>
                                    <span class="activity-time">{{ $play->played_at->setTimezone('Europe/Amsterdam')->format('M j, H:i') }} - {{ $play->played_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($allPlays->count() > 10)
                        <div style="margin-top: 1rem; text-align: center;">
                            <span style="color: #666; font-size: 0.875rem;">Showing 10 of {{ $allPlays->total() }} plays</span>
                        </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 1rem; color: #ccc;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p>No recent plays found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection