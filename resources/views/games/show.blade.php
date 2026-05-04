@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="flex: 1;">
                <div style="display: flex; align-items: start; gap: 1.5rem;">
                    @if($game->cover_image)
                        <img src="{{ $game->cover_image }}" alt="{{ $game->name }}" class="game-detail-cover">
                    @else
                        <div class="game-detail-cover game-detail-cover-placeholder">
                            <i class="fas fa-gamepad"></i>
                        </div>
                    @endif
                    <div>
                        <h1>{{ $game->name }}</h1>
                        <ul class="breadcrumb">
                            <li><a href="{{ route('home.index') }}">Home</a></li>
                            <li><a href="{{ route('games.index') }}">Games</a></li>
                            <li>{{ $game->name }}</li>
                        </ul>
                        @if($game->developer || $game->publisher)
                            <p style="margin-top: 0.5rem; color: #6b7280;">
                                @if($game->developer)
                                    <strong>Developer:</strong> {{ $game->developer }}
                                @endif
                                @if($game->publisher)
                                    @if($game->developer) &nbsp;|&nbsp; @endif
                                    <strong>Publisher:</strong> {{ $game->publisher }}
                                @endif
                            </p>
                        @endif
                        @if($game->description)
                            <p style="margin-top: 0.5rem; color: #6b7280;">{{ $game->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('games.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Games
                </a>
            </div>
        </div>
    </div>

    <!-- Game Statistics -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['total_plays']) }}</h3>
                        <p class="stat-label">Total Plays</p>
                        <span class="stat-change">{{ number_format($stats['unique_tracks']) }} unique tracks</span>
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
                        <h3 class="stat-value">{{ number_format(round($stats['total_duration_ms'] / 1000 / 60 / 60, 1), 1) }}</h3>
                        <p class="stat-label">Hours Listened</p>
                        <span class="stat-change">{{ number_format(round($stats['total_duration_ms'] / 1000 / 60, 0)) }} minutes</span>
                    </div>
                </div>
            </div>
        </div>

        @if($stats['first_played'])
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['first_played']->format('M j') }}</h3>
                        <p class="stat-label">First Played</p>
                        <span class="stat-change">{{ $stats['first_played']->format('Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($stats['last_played'])
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['last_played']->format('M j') }}</h3>
                        <p class="stat-label">Last Played</p>
                        <span class="stat-change">{{ $stats['last_played']->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Tracks List -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Soundtrack</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">All tracks from {{ $game->name }}</p>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($tracks->isEmpty())
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-music" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">No tracks found for this game.</p>
                </div>
            @else
                <div class="track-list">
                    @foreach($tracks as $index => $track)
                        <div class="track-item">
                            <div class="track-number">{{ $index + 1 }}</div>
                            <div class="track-image">
                                @if($track->album_image_url)
                                    <img src="{{ $track->album_image_url }}" alt="{{ $track->album_name }}">
                                @else
                                    <div class="track-placeholder">
                                        <i class="fas fa-music"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="track-info">
                                @if($track->spotify_track_id)
                                    <a href="{{ route('tracks.show', $track->spotify_track_id) }}" class="track-name">
                                        {{ $track->track_name }}
                                    </a>
                                @else
                                    <span class="track-name">{{ $track->track_name }}</span>
                                @endif
                                <div class="track-artist">{{ $track->artists_string }}</div>
                                @if(!empty($track->moods))
                                    <div class="track-moods">
                                        @foreach($track->moods as $mood)
                                            <span class="mood-badge" style="background-color: {{ $mood['color'] }}15; color: {{ $mood['color'] }}; border-color: {{ $mood['color'] }}40;">
                                                @if($mood['icon'])
                                                    <i class="{{ $mood['icon'] }}"></i>
                                                @endif
                                                {{ $mood['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="track-stats">
                                <div class="track-play-count">
                                    <i class="fas fa-play"></i>
                                    {{ number_format($track->play_count) }}
                                </div>
                            </div>
                            <div class="track-duration">
                                {{ gmdate('i:s', round($track->duration_ms / 1000)) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
