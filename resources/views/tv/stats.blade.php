@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>TV Series Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('tv.index') }}">TV Series</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('tv.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to TV Series
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
                    <span class="stat-change">{{ number_format($stats['overview']['total_episodes_watched']) }} episodes</span>
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
                        <i class="fas fa-tv"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_series']) }}</h3>
                        <p class="stat-label">Total Series</p>
                        <span class="stat-change">{{ number_format($stats['overview']['in_progress']) }} in progress</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-film"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_episodes_watched']) }}</h3>
                        <p class="stat-label">Episodes Watched</p>
                        <span class="stat-change">of {{ number_format($stats['overview']['total_episodes']) }} total</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['completion_percentage']) }}%</h3>
                        <p class="stat-label">Overall Progress</p>
                        <span class="stat-change">{{ number_format($stats['overview']['fully_completed']) }} completed</span>
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
                    <a href="{{ route('tv.show', $firstWatch['series_id']) }}" style="display: flex; gap: 1rem; align-items: center; text-decoration: none; color: inherit;">
                        @if($firstWatch['poster_url'])
                            <img src="{{ $firstWatch['poster_url'] }}" alt="{{ $firstWatch['series_name'] }}" style="width: 80px; height: 120px; border-radius: 0.5rem; object-fit: cover;">
                        @else
                            <div style="width: 80px; height: 120px; background: #374151; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tv" style="font-size: 2rem; color: #6b7280;"></i>
                            </div>
                        @endif
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem; color: #f3f4f6;">{{ $firstWatch['series_name'] }}</div>
                            <div style="color: #9ca3af; margin-bottom: 0.25rem;">S{{ sprintf('%02d', $firstWatch['season_number']) }}E{{ sprintf('%02d', $firstWatch['episode_number']) }} - {{ $firstWatch['episode_name'] }}</div>
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
                    <a href="{{ route('tv.show', $lastWatch['series_id']) }}" style="display: flex; gap: 1rem; align-items: center; text-decoration: none; color: inherit;">
                        @if($lastWatch['poster_url'])
                            <img src="{{ $lastWatch['poster_url'] }}" alt="{{ $lastWatch['series_name'] }}" style="width: 80px; height: 120px; border-radius: 0.5rem; object-fit: cover;">
                        @else
                            <div style="width: 80px; height: 120px; background: #374151; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tv" style="font-size: 2rem; color: #6b7280;"></i>
                            </div>
                        @endif
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem; color: #f3f4f6;">{{ $lastWatch['series_name'] }}</div>
                            <div style="color: #9ca3af; margin-bottom: 0.25rem;">S{{ sprintf('%02d', $lastWatch['season_number']) }}E{{ sprintf('%02d', $lastWatch['episode_number']) }} - {{ $lastWatch['episode_name'] }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem;">
                                <i class="fas fa-calendar"></i> {{ $lastWatch['watched_at']->format('M d, Y') }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Top Series -->
    @if(count($stats['top_series']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Most Watched Series</h3>
            </div>
            <div class="card-body">
                <div class="games-grid" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                    @foreach($stats['top_series'] as $series)
                        <a href="{{ route('tv.show', $series['id']) }}" class="game-card tv-card-small">
                            @if($series['poster_url'])
                                <div class="game-cover movie-poster">
                                    <img src="{{ $series['poster_url'] }}" alt="{{ $series['name'] }}">
                                    <div class="completion-badge">{{ number_format($series['completion_percentage']) }}%</div>
                                </div>
                            @else
                                <div class="game-cover game-cover-placeholder">
                                    <i class="fas fa-tv"></i>
                                </div>
                            @endif
                            <div class="game-info">
                                <h4 class="game-title" style="font-size: 0.875rem;">{{ Str::limit($series['name'], 30) }}</h4>
                                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                    {{ $series['episodes_watched'] }}/{{ $series['number_of_episodes'] }} episodes
                                </p>
                                <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; background: #10b981; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                        {{ $series['total_watches'] }} plays
                                    </span>
                                    <span style="display: inline-block; padding: 0.25rem 0.5rem; background: #3b82f6; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                        {{ $series['total_hours'] }}h
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Completion Progress -->
    @if(count($stats['completion_stats']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Completion Progress</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($stats['completion_stats'] as $series)
                        <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; color: #f3f4f6;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <div style="font-weight: 500;">{{ $series['name'] }}</div>
                                <div style="color: #10b981; font-weight: 600;">{{ number_format($series['completion_percentage']) }}%</div>
                            </div>
                            <div style="height: 8px; background: #374151; border-radius: 9999px; overflow: hidden;">
                                <div style="height: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); width: {{ $series['completion_percentage'] }}%; transition: width 0.3s;"></div>
                            </div>
                            <div style="color: #6b7280; font-size: 0.75rem; margin-top: 0.5rem;">
                                {{ $series['episodes_watched'] }} of {{ $series['number_of_episodes'] }} episodes
                            </div>
                        </div>
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
                            <div style="font-size: 0.75rem; color: #6b7280;">series</div>
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
                                <div style="color: #10b981; font-weight: 600;">{{ $day['count'] }} episode{{ $day['count'] != 1 ? 's' : '' }}</div>
                            </div>
                            <div style="color: #9ca3af; font-size: 0.875rem;">
                                @foreach($day['episodes'] as $episode)
                                    <div style="margin-bottom: 0.25rem;">{{ $episode }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Viewing Patterns --}}
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Viewing Patterns</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Most Active Day</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">{{ $stats['viewing_patterns']['most_active_day'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">{{ $stats['viewing_patterns']['most_active_day_count'] }} episodes</div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Longest Streak</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">{{ $stats['viewing_patterns']['longest_streak'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">consecutive days</div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Avg Episodes/Day</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">{{ $stats['viewing_patterns']['avg_episodes_per_day'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">on days watched</div>
                </div>
            </div>

            @if(count($stats['viewing_patterns']['binge_sessions']) > 0)
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Top Binge Sessions (5+ episodes)</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        @foreach($stats['viewing_patterns']['binge_sessions'] as $session)
                            <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; display: flex; justify-content: space-between; color: #f3f4f6;">
                                <span>{{ $session['date'] }}</span>
                                <span style="color: #10b981; font-weight: 600;">{{ $session['count'] }} episodes</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Time-Based Stats --}}
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Time-Based Statistics</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Busiest Month</div>
                    <div style="font-size: 1.25rem; font-weight: 600;">{{ $stats['time_based']['busiest_month'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">{{ $stats['time_based']['busiest_month_count'] }} episodes</div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Busiest Year</div>
                    <div style="font-size: 1.25rem; font-weight: 600;">{{ $stats['time_based']['busiest_year'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">{{ $stats['time_based']['busiest_year_count'] }} episodes</div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Avg Episode Length</div>
                    <div style="font-size: 1.25rem; font-weight: 600;">{{ $stats['time_based']['avg_episode_length'] }} min</div>
                </div>
            </div>

            @if(count($stats['time_based']['watch_time_by_year']) > 0)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Watch Time by Year</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        @foreach($stats['time_based']['watch_time_by_year'] as $year => $hours)
                            <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; display: flex; justify-content: space-between; color: #f3f4f6;">
                                <span>{{ $year }}</span>
                                <span style="color: #3b82f6; font-weight: 600;">{{ $hours }}h</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($stats['time_based']['most_rewatched']) > 0)
                <div>
                    <h4 style="margin-bottom: 1rem;">Most Rewatched Episodes</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        @foreach($stats['time_based']['most_rewatched'] as $episode)
                            <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; color: #f3f4f6;">
                                <div style="font-weight: 600;">{{ $episode['series_name'] }}</div>
                                <div style="color: #9ca3af; font-size: 0.875rem;">
                                    S{{ sprintf('%02d', $episode['season_number']) }}E{{ sprintf('%02d', $episode['episode_number']) }}: {{ $episode['episode_name'] }}
                                </div>
                                <div style="color: #10b981; font-size: 0.875rem; margin-top: 0.25rem;">{{ $episode['watch_count'] }} watches</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Content Breakdown --}}
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Content Breakdown</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Completion Rate</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">{{ $stats['content_breakdown']['completion_rate'] }}%</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">{{ $stats['content_breakdown']['completed_series'] }}/{{ $stats['content_breakdown']['started_series'] }} series</div>
                </div>
                <div>
                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Most Popular Decade</div>
                    <div style="font-size: 1.25rem; font-weight: 600;">{{ $stats['content_breakdown']['most_popular_decade'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.875rem;">{{ $stats['content_breakdown']['most_popular_decade_count'] }} series</div>
                </div>
            </div>

            <div>
                <h4 style="margin-bottom: 1rem;">Episode Count Distribution</h4>
                <div style="display: grid; gap: 0.5rem;">
                    @foreach($stats['content_breakdown']['episode_distribution'] as $range => $count)
                        <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; display: flex; justify-content: space-between; color: #f3f4f6;">
                            <span>{{ $range }} episodes</span>
                            <span style="color: #10b981; font-weight: 600;">{{ $count }} series</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Personal Records --}}
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Personal Records</h3>
        </div>
        <div class="card-body">
            @if(count($stats['personal_records']['episodes_by_year']) > 0)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem;">First & Last Episodes by Year</h4>
                    <div style="display: grid; gap: 1rem;">
                        @foreach($stats['personal_records']['episodes_by_year'] as $year => $data)
                            <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; color: #f3f4f6;">
                                <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $year }}</div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <div style="color: #6b7280; font-size: 0.875rem;">First</div>
                                        <div style="font-size: 0.875rem;">{{ $data['first']['series_name'] }}</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem;">{{ $data['first']['episode_name'] }}</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem;">{{ $data['first']['date'] }}</div>
                                    </div>
                                    <div>
                                        <div style="color: #6b7280; font-size: 0.875rem;">Last</div>
                                        <div style="font-size: 0.875rem;">{{ $data['last']['series_name'] }}</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem;">{{ $data['last']['episode_name'] }}</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem;">{{ $data['last']['date'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($stats['personal_records']['highest_rated']) > 0)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Highest Rated Shows Watched</h4>
                    <div class="games-grid">
                        @foreach($stats['personal_records']['highest_rated'] as $series)
                            <a href="{{ route('tv.show', $series['id']) }}" class="tv-card-small">
                                @if($series['poster_url'])
                                    <div class="game-cover">
                                        <img src="{{ $series['poster_url'] }}" alt="{{ $series['name'] }}">
                                    </div>
                                @endif
                                <div style="margin-top: 0.5rem;">
                                    <div style="font-weight: 600; font-size: 0.875rem;">{{ $series['name'] }}</div>
                                    <div style="color: #fbbf24; font-size: 0.875rem; margin-top: 0.25rem;">
                                        <i class="fas fa-star"></i> {{ number_format($series['vote_average'], 1) }}/10
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($stats['personal_records']['longest_completed']) > 0)
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Longest Series Completed</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        @foreach($stats['personal_records']['longest_completed'] as $series)
                            <a href="{{ route('tv.show', $series['id']) }}" style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
                                <span>{{ $series['name'] }}</span>
                                <span style="color: #10b981; font-weight: 600;">{{ $series['number_of_episodes'] }} episodes</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($stats['personal_records']['fastest_binge']) > 0)
                <div>
                    <h4 style="margin-bottom: 1rem;">Fastest Binges</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        @foreach($stats['personal_records']['fastest_binge'] as $series)
                            <a href="{{ route('tv.show', $series['id']) }}" style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; text-decoration: none; color: inherit;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span>{{ $series['name'] }}</span>
                                    <span style="color: #3b82f6; font-weight: 600;">{{ $series['days'] }} day{{ $series['days'] != 1 ? 's' : '' }}</span>
                                </div>
                                <div style="color: #9ca3af; font-size: 0.875rem; margin-top: 0.25rem;">{{ $series['episodes'] }} episodes</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Comparisons --}}
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-balance-scale"></i> Comparisons</h3>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem;">Watched vs Unwatched Time</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem;">
                        <div style="color: #6b7280; font-size: 0.875rem;">Watched</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">{{ $stats['comparisons']['watched_hours'] }}h</div>
                        <div style="color: #9ca3af; font-size: 0.875rem;">{{ $stats['comparisons']['watched_percentage'] }}%</div>
                    </div>
                    <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem;">
                        <div style="color: #6b7280; font-size: 0.875rem;">Unwatched</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: #6b7280;">{{ $stats['comparisons']['unwatched_hours'] }}h</div>
                        <div style="color: #9ca3af; font-size: 0.875rem;">{{ 100 - $stats['comparisons']['watched_percentage'] }}%</div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem;">Season Completion</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">{{ $stats['comparisons']['fully_watched_seasons'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.875rem;">Fully Watched</div>
                    </div>
                    <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: #fbbf24;">{{ $stats['comparisons']['partially_watched_seasons'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.875rem;">In Progress</div>
                    </div>
                    <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 600; color: #6b7280;">{{ $stats['comparisons']['unwatched_seasons'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.875rem;">Unwatched</div>
                    </div>
                </div>
            </div>

            @if(count($stats['comparisons']['year_over_year_growth']) > 0)
                <div>
                    <h4 style="margin-bottom: 1rem;">Year-over-Year Growth</h4>
                    <div style="display: grid; gap: 0.5rem;">
                        @foreach($stats['comparisons']['year_over_year_growth'] as $year => $data)
                            <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; display: flex; justify-content: space-between; align-items: center; color: #f3f4f6;">
                                <div>
                                    <span style="font-weight: 600;">{{ $year }}</span>
                                    <span style="color: #9ca3af; font-size: 0.875rem; margin-left: 0.5rem;">{{ $data['count'] }} episodes</span>
                                </div>
                                <div style="font-weight: 600; {{ $data['growth'] >= 0 ? 'color: #10b981;' : 'color: #ef4444;' }}">
                                    {{ $data['growth'] >= 0 ? '+' : '' }}{{ $data['growth'] }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.tv-card-small {
    display: block;
    text-decoration: none;
    color: inherit;
}

.tv-card-small .game-cover {
    aspect-ratio: 2/3;
    position: relative;
}

.completion-badge {
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
</style>
@endsection
