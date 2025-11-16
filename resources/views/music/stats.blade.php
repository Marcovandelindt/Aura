@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Music Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('music.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Music
                </a>
            </div>
        </div>
    </div>
    
    <!-- Overall Statistics -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['listening_stats']['total_tracks']) }}</h3>
                        <p class="stat-label">Total Tracks Played</p>
                        <span class="stat-change">{{ number_format($stats['listening_stats']['unique_tracks']) }} unique songs</span>
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
                        <h3 class="stat-value">{{ $stats['listening_stats']['total_duration_hours'] }}</h3>
                        <p class="stat-label">Hours Listened</p>
                        <span class="stat-change">{{ $stats['listening_stats']['average_per_day'] }} tracks/day avg</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['listening_stats']['tracks_this_week'] }}</h3>
                        <p class="stat-label">This Week</p>
                        <span class="stat-change">{{ $stats['listening_stats']['tracks_today'] }} today</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['listening_stats']['tracks_this_month'] }}</h3>
                        <p class="stat-label">This Month</p>
                        @if($stats['listening_stats']['first_track_date'])
                            <span class="stat-change">Since {{ $stats['listening_stats']['first_track_date']->format('M j, Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Lists Grid -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Top Artists -->
        <div class="card">
            <div class="card-header">
                <h3>Top Artists</h3>
            </div>
            <div class="card-body">
                @if(count($stats['top_artists']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_artists'] as $index => $artist)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">
                                        <a href="{{ route('artists.show', ['artist' => urlencode($artist['name'])]) }}" class="item-link">
                                            {{ $artist['name'] }}
                                        </a>
                                    </div>
                                    <div class="item-details">
                                        {{ $artist['play_count'] }} plays • 
                                        {{ $artist['unique_tracks_count'] }} songs • 
                                        {{ round($artist['total_duration_ms'] / 1000 / 60 / 60, 1) }}h
                                    </div>
                                </div>
                                <div class="play-count">{{ $artist['play_count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No artist data available yet. Start listening to music!</p>
                @endif
            </div>
        </div>
        
        <!-- Top Tracks -->
        <div class="card">
            <div class="card-header">
                <h3>Top Tracks</h3>
            </div>
            <div class="card-body">
                @if(count($stats['top_tracks']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_tracks'] as $index => $track)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="track-cover">
                                    @if($track['album_image_url'])
                                        <img src="{{ $track['album_image_url'] }}" alt="{{ $track['album_name'] }}" class="cover-image">
                                    @else
                                        <div class="cover-placeholder">
                                            <i class="fas fa-music"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <div class="item-name">
                                        <a href="{{ route('tracks.show', ['track' => $track['spotify_track_id']]) }}" class="item-link">
                                            {{ $track['track_name'] }}
                                        </a>
                                    </div>
                                    <div class="item-details">
                                        {{ $track['artists_string'] }} • {{ $track['album_name'] }}
                                    </div>
                                </div>
                                <div class="play-count">{{ $track['play_count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No track data available yet. Start listening to music!</p>
                @endif
            </div>
        </div>
        
        <!-- Top Albums -->
        <div class="card">
            <div class="card-header">
                <h3>Top Albums</h3>
            </div>
            <div class="card-body">
                @if(count($stats['top_albums']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_albums'] as $index => $album)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="track-cover">
                                    @if($album['album_image_url'])
                                        <img src="{{ $album['album_image_url'] }}" alt="{{ $album['album_name'] }}" class="cover-image">
                                    @else
                                        <div class="cover-placeholder">
                                            <i class="fas fa-compact-disc"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <div class="item-name">
                                        <a href="{{ route('albums.show', ['album' => urlencode($album['album_name'])]) }}" class="item-link">
                                            {{ $album['album_name'] }}
                                        </a>
                                    </div>
                                    <div class="item-details">
                                        {{ $album['artists_string'] }} • 
                                        {{ $album['unique_tracks_count'] }} tracks
                                    </div>
                                </div>
                                <div class="play-count">{{ $album['play_count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No album data available yet. Start listening to music!</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection