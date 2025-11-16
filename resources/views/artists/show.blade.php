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
                <h1>{{ $artistName }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>{{ $artistName }}</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Artist Info Header -->
    @if($topTrack)
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <div class="artist-header">
                    @if($topTrack->album_image_url)
                        <img src="{{ $topTrack->album_image_url }}" 
                             alt="{{ $artistName }}"
                             class="artist-image">
                    @endif
                    
                    <div class="artist-info">
                        <div class="artist-type-label">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Artist</span>
                        </div>
                        
                        <h2 class="artist-name">{{ $artistName }}</h2>
                        
                        <div class="artist-stats-inline">
                            <div class="artist-stat-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                                <span>{{ $stats['unique_tracks'] }} unique tracks</span>
                            </div>
                            
                            <div class="artist-stat-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                                @php
                                    $totalMinutes = floor($stats['total_duration_ms'] / 1000 / 60);
                                    $hours = floor($totalMinutes / 60);
                                    $minutes = $totalMinutes % 60;
                                @endphp
                                <span>{{ $hours }}h {{ $minutes }}m total listening time</span>
                            </div>
                        </div>
                        
                        <div class="artist-top-track">
                            <p class="top-track-label"><strong>Most played track:</strong></p>
                            <p class="top-track-name">{{ $topTrack->track_name }} ({{ $topTrack->play_count }} plays)</p>
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
        <!-- Top Tracks -->
        <div class="card">
            <div class="card-header">
                <h3>Top Tracks by {{ $artistName }}</h3>
            </div>
            <div class="card-body">
                @if($uniqueTracks->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="50"></th>
                                    <th>Track</th>
                                    <th>Album</th>
                                    <th>Plays</th>
                                    <th>Last Played</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uniqueTracks as $track)
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
                                    </td>
                                    <td>{{ $track->album_name }}</td>
                                    <td>
                                        <span style="background: linear-gradient(135deg, #1DB954 0%, #1ed760 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                                            {{ $track->play_count }} plays
                                        </span>
                                    </td>
                                    <td style="color: #666;">{{ $track->last_played ? \Carbon\Carbon::parse($track->last_played)->setTimezone('Europe/Amsterdam')->format('M j, H:i') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for tracks -->
                    @if($uniqueTracks->hasPages())
                        <div style="margin-top: 1rem;">
                            {{ $uniqueTracks->links() }}
                        </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 1rem; color: #ccc;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p>No tracks found for this artist.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Plays -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Plays</h3>
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
                                        <a href="{{ route('tracks.show', ['track' => $play->spotify_track_id]) }}" 
                                           style="color: inherit; text-decoration: none;">
                                            <strong>{{ $play->track_name }}</strong>
                                        </a>
                                        from <em>{{ $play->album_name }}</em>
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