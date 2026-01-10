@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Dashboard</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Dashboard</li>
                </ul>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #888;">
                @if($spotifyConnected)
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#1DB954" class="spotify-connected">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <span>Spotify connected</span>
                @else
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#ccc">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <a href="{{ route('spotify.auth') }}" style="color: #888; text-decoration: none; hover: #666;">Connect Spotify</a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $songsThisWeek['count'] }}</h3>
                        <p class="stat-label">Songs This Week</p>
                        <span class="stat-change {{ $songsThisWeek['change_class'] }}">{{ $songsThisWeek['change_text'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">3</h3>
                        <p class="stat-label">Movies/Shows This Week</p>
                        <span class="stat-change">2 movies, 1 series</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">Good</h3>
                        <p class="stat-label">Current Mood</p>
                        <span class="stat-change positive">Better than yesterday</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">7/12</h3>
                        <p class="stat-label">Tasks Completed Today</p>
                        <span class="stat-change">58% completion rate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Grid -->
    <div class="content-grid">
        <div class="card">
            <div class="card-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    {{-- Last Played Track --}}
                    @if($lastPlayedTrack)
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #1DB954 0%, #169c46 100%);">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>Listened to
                                    <a href="{{ route('tracks.show', ['track' => $lastPlayedTrack->spotify_track_id]) }}" class="activity-link">
                                        <strong>{{ $lastPlayedTrack->track_name }}</strong>
                                    </a>
                                    by
                                    @foreach($lastPlayedTrack->artist_names as $index => $artistName)
                                        <a href="{{ route('artists.show', ['artist' => urlencode($artistName)]) }}" class="activity-link">{{ $artistName }}</a>@if($index < count($lastPlayedTrack->artist_names) - 1), @endif
                                    @endforeach
                                </p>
                                <span class="activity-time">{{ $lastPlayedTrack->played_at_human }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Last Episode Watched --}}
                    @if($lastEpisodeWatch)
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #e50914 0%, #b20710 100%);">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm3 2h6v4H7V5zm8 8v2h1v-2h-1zm-2-2H7v4h6v-4zm2 0h1V9h-1v2zm1-4V5h-1v2h1zM5 5v2H4V5h1zm0 4H4v2h1V9zm-1 4h1v2H4v-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>Watched <a href="{{ route('tv.show', $lastEpisodeWatch->episode->season->series) }}" class="activity-link"><strong>{{ $lastEpisodeWatch->episode->season->series->name }}</strong></a>
                                    - S{{ str_pad($lastEpisodeWatch->episode->season->season_number, 2, '0', STR_PAD_LEFT) }}E{{ str_pad($lastEpisodeWatch->episode->episode_number, 2, '0', STR_PAD_LEFT) }}
                                </p>
                                <span class="activity-time">{{ $lastEpisodeWatch->watched_at->isToday() ? 'Today' : $lastEpisodeWatch->watched_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Last Game Session --}}
                    @if($lastGameSession)
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #006FCD 0%, #00439C 100%);">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM6.5 9a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm7 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm-7 4a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm7 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>Played <a href="{{ route('playstation.index') }}" class="activity-link"><strong>{{ $lastGameSession->game->name }}</strong></a>
                                    @if($lastGameSession->duration_minutes)
                                        for {{ $lastGameSession->formatted_duration }}
                                    @endif
                                </p>
                                <span class="activity-time">{{ $lastGameSession->started_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Last Health Entry --}}
                    @if($lastHealthEntry)
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>Walked <a href="{{ route('health.index') }}" class="activity-link"><strong>{{ $lastHealthEntry->formatted_steps }} steps</strong></a>
                                    @if($lastHealthEntry->meetsStepGoal())
                                        <span style="color: #10b981; margin-left: 4px;">Goal reached!</span>
                                    @endif
                                </p>
                                <span class="activity-time">{{ $lastHealthEntry->date->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Empty state if no activities --}}
                    @if(!$lastPlayedTrack && !$lastEpisodeWatch && !$lastGameSession && !$lastHealthEntry)
                        <div class="activity-item">
                            <div class="activity-icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>No recent activity yet</p>
                                <span class="activity-time">Start tracking to see your activity here</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="#" class="quick-action-btn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>Log Music</span>
                    </a>
                    <a href="#" class="quick-action-btn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Add Movie/Show</span>
                    </a>
                    <a href="#" class="quick-action-btn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        <span>Track Mood</span>
                    </a>
                    <a href="#" class="quick-action-btn">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 00-2 2v6a2 2 0 002 2h2a1 1 0 000 2H6a4 4 0 01-4-4V5zm3 1a1 1 0 011-1h8a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h8a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h8a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>Create Task</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection