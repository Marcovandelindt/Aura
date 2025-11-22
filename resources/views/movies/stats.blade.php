@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Movie Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('movies.index') }}">Movies</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('movies.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Movies
                </a>
            </div>
        </div>
    </div>

    <!-- Total Watch Time -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div class="stat-content" style="justify-content: center;">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); width: 80px; height: 80px;">
                    <i class="fas fa-clock" style="font-size: 2rem;"></i>
                </div>
                <div class="stat-details" style="text-align: center;">
                    <h3 class="stat-value" style="font-size: 3rem;">{{ number_format($stats['overview']['total_hours']) }}</h3>
                    <p class="stat-label" style="font-size: 1.25rem;">Total Hours Watched</p>
                    <span class="stat-change">{{ number_format($stats['overview']['total_watches']) }} total watches</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-film"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_movies']) }}</h3>
                        <p class="stat-label">Total Movies</p>
                        <span class="stat-change">{{ number_format($stats['overview']['unique_watched']) }} watched</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_watches']) }}</h3>
                        <p class="stat-label">Total Watches</p>
                        <span class="stat-change">{{ number_format($stats['overview']['unique_watched']) }} unique movies</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['avg_rating'], 1) }}</h3>
                        <p class="stat-label">Average Rating</p>
                        <span class="stat-change">TMDB rating</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- First & Last Watch -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
        @if($stats['overview']['first_watch'])
            <!-- First Watch -->
            @php
                $firstWatch = $stats['overview']['first_watch'];
            @endphp
            <div class="card">
                <div class="card-header">
                    <h3>First Watch Ever</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('movies.show', $firstWatch['movie_id']) }}" style="display: flex; gap: 1rem; align-items: center; text-decoration: none; color: inherit;">
                        @if($firstWatch['poster_url'])
                            <img src="{{ $firstWatch['poster_url'] }}" alt="{{ $firstWatch['movie_title'] }}" style="width: 80px; height: 120px; border-radius: 0.5rem; object-fit: cover;">
                        @else
                            <div style="width: 80px; height: 120px; background: #374151; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-film" style="font-size: 2rem; color: #6b7280;"></i>
                            </div>
                        @endif
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem; color: #f3f4f6;">{{ $firstWatch['movie_title'] }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem;">
                                <i class="fas fa-calendar"></i> {{ $firstWatch['watched_at']->format('M d, Y') }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif

        @if($stats['overview']['last_watch'])
            <!-- Last Watch -->
            @php
                $lastWatch = $stats['overview']['last_watch'];
            @endphp
            <div class="card">
                <div class="card-header">
                    <h3>Last Watch</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('movies.show', $lastWatch['movie_id']) }}" style="display: flex; gap: 1rem; align-items: center; text-decoration: none; color: inherit;">
                        @if($lastWatch['poster_url'])
                            <img src="{{ $lastWatch['poster_url'] }}" alt="{{ $lastWatch['movie_title'] }}" style="width: 80px; height: 120px; border-radius: 0.5rem; object-fit: cover;">
                        @else
                            <div style="width: 80px; height: 120px; background: #374151; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-film" style="font-size: 2rem; color: #6b7280;"></i>
                            </div>
                        @endif
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem; color: #f3f4f6;">{{ $lastWatch['movie_title'] }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem;">
                                <i class="fas fa-calendar"></i> {{ $lastWatch['watched_at']->format('M d, Y') }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Top Movies -->
    @if(count($stats['top_movies']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Most Watched Movies</h3>
            </div>
            <div class="card-body">
                <div class="games-grid" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                    @foreach($stats['top_movies'] as $movie)
                        <a href="{{ route('movies.show', $movie['id']) }}" class="game-card movie-card-small">
                            @if($movie['poster_url'])
                                <div class="game-cover movie-poster">
                                    <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}">
                                    <div class="watch-count-badge">{{ $movie['watch_count'] }}x</div>
                                </div>
                            @else
                                <div class="game-cover game-cover-placeholder">
                                    <i class="fas fa-film"></i>
                                </div>
                            @endif
                            <div class="game-info">
                                <h4 class="game-title" style="font-size: 0.875rem;">{{ Str::limit($movie['title'], 30) }}</h4>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Genre Distribution -->
    @if(count($stats['genre_stats']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Genre Distribution</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    @foreach(array_slice($stats['genre_stats'], 0, 10) as $genre)
                        <div style="flex: 1; min-width: 150px; padding: 1rem; background: #1f2937; border-radius: 0.5rem; color: #f3f4f6;">
                            <div style="font-weight: 500; margin-bottom: 0.5rem;">{{ $genre['genre'] }}</div>
                            <div style="font-size: 1.5rem; color: #10b981;">{{ $genre['count'] }}</div>
                            <div style="font-size: 0.75rem; color: #6b7280;">movies</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Watch History -->
    @if(count($stats['watch_history']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Recent Watch History</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach(array_slice($stats['watch_history'], 0, 10) as $day)
                        <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; color: #f3f4f6;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="font-weight: 500;">{{ $day['date_formatted'] }}</div>
                                <div style="color: #10b981; font-weight: 600;">{{ $day['count'] }} movie{{ $day['count'] != 1 ? 's' : '' }}</div>
                            </div>
                            <div style="color: #9ca3af; font-size: 0.875rem;">
                                {{ implode(', ', $day['movies']) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.watch-count-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(16, 185, 129, 0.9);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.movie-card-small {
    display: block;
    text-decoration: none;
    color: inherit;
}

.movie-card-small .game-cover {
    aspect-ratio: 2/3;
    position: relative;
}
</style>
@endsection
