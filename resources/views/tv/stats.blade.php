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

    <!-- Watch Time Chart -->
    @if(count($stats['watch_time_chart']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <h3 style="margin: 0;"><i class="fas fa-chart-bar" style="margin-right: 8px;"></i> Watch Time Per Day</h3>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <button class="chart-period-btn active" data-period="30">30 days</button>
                    <button class="chart-period-btn" data-period="60">60 days</button>
                    <button class="chart-period-btn" data-period="90">90 days</button>
                    <button class="chart-period-btn" data-period="180">180 days</button>
                    <button class="chart-period-btn" data-period="360">360 days</button>
                    <button class="chart-period-btn" data-period="custom">Custom</button>
                </div>
            </div>
            <div id="customRangeWrapper" style="display: none; padding: 0.75rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); align-items: center; gap: 1rem; flex-wrap: wrap;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    From
                    <input type="text" id="customStart" placeholder="DD-MM-YYYY" style="background: #fff; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.25rem 0.5rem; color: #111827; font-size: 0.875rem; width: 110px;">
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                    To
                    <input type="text" id="customEnd" placeholder="DD-MM-YYYY" style="background: #fff; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.25rem 0.5rem; color: #111827; font-size: 0.875rem; width: 110px;">
                </label>
                <button id="applyCustomRange" class="btn btn-sm btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Apply</button>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: center; gap: 3rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <div id="chartTotalHours" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">0</div>
                        <div style="color: #9ca3af; font-size: 0.875rem; margin-top: 0.25rem;">total hours in period</div>
                    </div>
                    <div style="text-align: center;">
                        <div id="chartAvgHours" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">0</div>
                        <div style="color: #9ca3af; font-size: 0.875rem; margin-top: 0.25rem;">avg hours per day</div>
                    </div>
                </div>
                <canvas id="watchTimeChart" height="80"></canvas>
            </div>
        </div>
    @endif

    <!-- Activity Heatmap -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i> Activity Heatmap — Last 365 Days</h3>
        </div>
        <div class="card-body">
            <div id="heatmapContainer" style="overflow-x: auto;"></div>
        </div>
    </div>

    <!-- Hour Distribution + Weekend vs Weekday -->
    <div class="stats-grid" style="margin-top: 2rem;">
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3><i class="fas fa-clock" style="margin-right: 8px;"></i> Hour of Day Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="hourChart" height="80"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-week" style="margin-right: 8px;"></i> Weekday vs Weekend</h3>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; justify-content: center; gap: 1.5rem;">
                <div style="text-align: center;">
                    <div id="weekdayPct" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">—</div>
                    <div style="color: #9ca3af; font-size: 0.875rem; margin-top: 0.25rem;">weekday (Mon–Fri)</div>
                </div>
                <div style="height: 1px; background: rgba(255,255,255,0.08);"></div>
                <div style="text-align: center;">
                    <div id="weekendPct" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">—</div>
                    <div style="color: #9ca3af; font-size: 0.875rem; margin-top: 0.25rem;">weekend (Sat–Sun)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cumulative Watch Time -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-line" style="margin-right: 8px;"></i> Cumulative Watch Time</h3>
        </div>
        <div class="card-body">
            <canvas id="cumulativeChart" height="70"></canvas>
        </div>
    </div>

    <!-- Rating vs Watch Time Scatter -->
    @if(count($stats['top_series']) > 1)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-star" style="margin-right: 8px;"></i> Rating vs Watch Time</h3>
            </div>
            <div class="card-body">
                <canvas id="scatterChart" height="70"></canvas>
            </div>
        </div>
    @endif

    <!-- Abandonment Analysis -->
    @if(count($stats['abandonment']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-pause-circle" style="margin-right: 8px;"></i> Abandoned Series</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($stats['abandonment'] as $series)
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                                <a href="{{ route('tv.show', $series['id']) }}" style="color: inherit; text-decoration: none; font-size: 0.9rem; font-weight: 500; hover: underline;">
                                    {{ $series['name'] }}
                                </a>
                                <span style="font-size: 0.8rem; color: #9ca3af; white-space: nowrap; margin-left: 1rem;">
                                    {{ $series['episodes_watched'] }}/{{ $series['number_of_episodes'] }} eps &mdash; {{ number_format($series['completion_percentage'], 1) }}%
                                </span>
                            </div>
                            <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $series['completion_percentage'] }}%; background: linear-gradient(90deg, #fa709a 0%, #fee140 100%); border-radius: 3px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Time to Complete -->
    @if(count($stats['completion_time']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-stopwatch" style="margin-right: 8px;"></i> Days to Complete a Series</h3>
            </div>
            <div class="card-body">
                <div style="position: relative; height: {{ max(300, count($stats['completion_time']) * 32) }}px;">
                    <canvas id="completionTimeChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <!-- Longest Pause -->
    @if(count($stats['longest_pause']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-bed" style="margin-right: 8px;"></i> Longest Break Within a Series</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                    @foreach($stats['longest_pause'] as $i => $item)
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="width: 1.5rem; text-align: right; color: #9ca3af; font-size: 0.875rem; flex-shrink: 0;">{{ $i + 1 }}.</span>
                            <span style="flex: 1; font-size: 0.9rem;">{{ $item['series_name'] }}</span>
                            <span style="font-weight: 700; color: #fa709a; white-space: nowrap;">{{ number_format($item['days']) }} days</span>
                        </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const watchTimeData = @json($stats['watch_time_chart']);
    if (!watchTimeData.length) return;

    const ctx = document.getElementById('watchTimeChart');
    if (!ctx) return;

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Hours watched',
                data: [],
                backgroundColor: 'rgba(250, 112, 154, 0.5)',
                borderColor: '#fa709a',
                borderWidth: 1,
                borderRadius: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.raw.toFixed(2) + ' hrs',
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxTicksLimit: 12,
                        color: '#9ca3af',
                        font: { size: 11 },
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#9ca3af',
                        callback: val => val + ' hrs',
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' },
                }
            }
        }
    });

    function filterByDays(days) {
        const today = new Date();
        today.setHours(23, 59, 59, 999);
        const from = new Date();
        from.setDate(today.getDate() - days + 1);
        from.setHours(0, 0, 0, 0);
        return watchTimeData.filter(d => {
            const date = new Date(d.date);
            return date >= from && date <= today;
        });
    }

    function parseDMY(str) {
        const [d, m, y] = str.split('-');
        return new Date(`${y}-${m}-${d}`);
    }

    function filterByRange(startStr, endStr) {
        const from = parseDMY(startStr);
        const to = parseDMY(endStr);
        to.setHours(23, 59, 59, 999);
        return watchTimeData.filter(d => {
            const date = new Date(d.date);
            return date >= from && date <= to;
        });
    }

    function applyData(data) {
        const total = data.reduce((sum, d) => sum + d.hours, 0);
        const avg = data.length > 0 ? total / data.length : 0;
        document.getElementById('chartTotalHours').textContent = total.toFixed(1);
        document.getElementById('chartAvgHours').textContent = avg.toFixed(2);
        chart.data.labels = data.map(d => d.date);
        chart.data.datasets[0].data = data.map(d => d.hours);
        chart.update();
    }

    // Default: 30 days
    applyData(filterByDays(30));

    document.querySelectorAll('.chart-period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.chart-period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const period = this.dataset.period;
            const wrapper = document.getElementById('customRangeWrapper');

            if (period === 'custom') {
                wrapper.style.display = 'flex';
            } else {
                wrapper.style.display = 'none';
                applyData(filterByDays(parseInt(period)));
            }
        });
    });

    document.getElementById('applyCustomRange').addEventListener('click', function () {
        const start = document.getElementById('customStart').value;
        const end = document.getElementById('customEnd').value;
        if (start && end && parseDMY(start) <= parseDMY(end)) {
            applyData(filterByRange(start, end));
        }
    });
})();
</script>
<script>
(function () {
    // ── Shared colours ──────────────────────────────────────────────────────
    const PINK   = '#fa709a';
    const YELLOW = '#fee140';
    const PURPLE = '#764ba2';
    const BLUE   = '#667eea';
    const GRID   = 'rgba(255,255,255,0.05)';
    const TICK   = '#9ca3af';

    // ── 1. Activity Heatmap ─────────────────────────────────────────────────
    (function () {
        const data = @json($stats['heatmap']);
        const container = document.getElementById('heatmapContainer');
        if (!container || !data.length) return;

        const maxCount = Math.max(...data.map(d => d.count), 1);
        const COLORS = [
            'rgba(255,255,255,0.06)',
            'rgba(250,112,154,0.25)',
            'rgba(250,112,154,0.5)',
            'rgba(250,112,154,0.75)',
            'rgba(250,112,154,1)',
        ];
        const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        function cellColor(count) {
            if (count === 0) return COLORS[0];
            const ratio = count / maxCount;
            if (ratio < 0.2)  return COLORS[1];
            if (ratio < 0.45) return COLORS[2];
            if (ratio < 0.75) return COLORS[3];
            return COLORS[4];
        }

        // Pad first week so day 0 = Sunday
        const firstDow = data[0].day_of_week;
        const padded = [...Array(firstDow).fill(null), ...data];

        // Group into weeks
        const weeks = [];
        for (let i = 0; i < padded.length; i += 7) weeks.push(padded.slice(i, i + 7));

        // Month label per week
        const monthLabel = {};
        data.forEach((d, i) => {
            const dt = new Date(d.date);
            if (dt.getDate() <= 7) {
                const wk = Math.floor((i + firstDow) / 7);
                monthLabel[wk] = dt.toLocaleDateString('en-GB', { month: 'short' });
            }
        });

        const outer = document.createElement('div');
        outer.style.cssText = 'display:flex;gap:3px;align-items:flex-start;min-width:max-content;';

        // Day label column
        const labelCol = document.createElement('div');
        labelCol.style.cssText = 'display:flex;flex-direction:column;gap:3px;padding-top:20px;margin-right:2px;';
        DAY_LABELS.forEach(label => {
            const el = document.createElement('div');
            el.style.cssText = 'width:22px;height:11px;font-size:9px;color:#9ca3af;display:flex;align-items:center;';
            el.textContent = label;
            labelCol.appendChild(el);
        });
        outer.appendChild(labelCol);

        weeks.forEach((week, wkIdx) => {
            const col = document.createElement('div');
            col.style.cssText = 'display:flex;flex-direction:column;gap:3px;';

            const ml = document.createElement('div');
            ml.style.cssText = 'height:16px;font-size:9px;color:#9ca3af;white-space:nowrap;';
            ml.textContent = monthLabel[wkIdx] ?? '';
            col.appendChild(ml);

            for (let dow = 0; dow < 7; dow++) {
                const day = week[dow] ?? null;
                const cell = document.createElement('div');
                cell.style.cssText = `width:11px;height:11px;border-radius:2px;background:${day ? cellColor(day.count) : 'transparent'};`;
                if (day) {
                    cell.title = `${day.date}: ${day.count} episode${day.count !== 1 ? 's' : ''}`;
                    cell.style.cursor = 'default';
                }
                col.appendChild(cell);
            }
            outer.appendChild(col);
        });

        container.appendChild(outer);

        // Legend
        const legend = document.createElement('div');
        legend.style.cssText = 'display:flex;align-items:center;gap:4px;margin-top:0.75rem;justify-content:flex-end;';
        legend.innerHTML = `<span style="font-size:10px;color:#9ca3af;">Less</span>`;
        COLORS.forEach(c => {
            const sq = document.createElement('div');
            sq.style.cssText = `width:11px;height:11px;border-radius:2px;background:${c};border:1px solid rgba(255,255,255,0.08);`;
            legend.appendChild(sq);
        });
        legend.innerHTML += `<span style="font-size:10px;color:#9ca3af;">More</span>`;
        container.appendChild(legend);
    })();

    // ── 2. Hour of Day ──────────────────────────────────────────────────────
    (function () {
        const ctx = document.getElementById('hourChart');
        if (!ctx) return;
        const hours = @json($stats['hour_distribution']);
        const labels = Array.from({length: 24}, (_, i) => `${String(i).padStart(2,'0')}:00`);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Episodes',
                    data: hours,
                    backgroundColor: hours.map((_, i) => {
                        const t = i / 23;
                        return `rgba(${Math.round(250*(1-t)+102*t)}, ${Math.round(112*(1-t)+126*t)}, ${Math.round(154*(1-t)+238*t)}, 0.7)`;
                    }),
                    borderRadius: 3,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: TICK, font: { size: 10 } }, grid: { color: GRID } },
                    y: { beginAtZero: true, ticks: { color: TICK }, grid: { color: GRID } },
                },
            },
        });
    })();

    // ── 3. Weekday vs Weekend ───────────────────────────────────────────────
    (function () {
        const heatmap = @json($stats['heatmap']);
        let weekday = 0, weekend = 0;
        heatmap.forEach(d => {
            // day_of_week: 0=Sun, 6=Sat
            if (d.day_of_week === 0 || d.day_of_week === 6) weekend += d.count;
            else weekday += d.count;
        });
        const total = weekday + weekend || 1;
        const wdEl = document.getElementById('weekdayPct');
        const weEl = document.getElementById('weekendPct');
        if (wdEl) wdEl.textContent = Math.round(weekday / total * 100) + '%';
        if (weEl) weEl.textContent = Math.round(weekend / total * 100) + '%';
    })();

    // ── 4. Cumulative Watch Time ────────────────────────────────────────────
    (function () {
        const ctx = document.getElementById('cumulativeChart');
        if (!ctx) return;
        const raw = @json($stats['watch_time_chart']);
        let cumulative = 0;
        const data = raw.map(d => { cumulative += d.hours; return { x: d.date, y: +cumulative.toFixed(2) }; });

        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Total hours',
                    data,
                    borderColor: PINK,
                    backgroundColor: 'rgba(250,112,154,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                parsing: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.parsed.y.toFixed(1) + ' hrs total' } },
                },
                scales: {
                    x: {
                        type: 'category',
                        ticks: { maxTicksLimit: 12, color: TICK, font: { size: 10 } },
                        grid: { color: GRID },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: TICK, callback: v => v + ' hrs' },
                        grid: { color: GRID },
                    },
                },
            },
        });
    })();

    // ── 5. Rating vs Watch Time Scatter ─────────────────────────────────────
    (function () {
        const ctx = document.getElementById('scatterChart');
        if (!ctx) return;
        const series = @json($stats['top_series']);
        const points = series
            .filter(s => s.vote_average > 0 && s.total_hours > 0)
            .map(s => ({ x: s.vote_average, y: s.total_hours, label: s.name }));

        new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Series',
                    data: points,
                    backgroundColor: 'rgba(250,112,154,0.6)',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.raw.label} — ⭐ ${ctx.raw.x} / ${ctx.raw.y} hrs`,
                        },
                    },
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Rating (TMDB)', color: TICK },
                        ticks: { color: TICK },
                        grid: { color: GRID },
                    },
                    y: {
                        title: { display: true, text: 'Hours watched', color: TICK },
                        beginAtZero: true,
                        ticks: { color: TICK, callback: v => v + ' hrs' },
                        grid: { color: GRID },
                    },
                },
            },
        });
    })();

    // ── 6. Days to Complete ─────────────────────────────────────────────────
    (function () {
        const ctx = document.getElementById('completionTimeChart');
        if (!ctx) return;
        const data = @json($stats['completion_time']);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.name),
                datasets: [{
                    label: 'Days',
                    data: data.map(d => d.days),
                    backgroundColor: 'rgba(102,126,234,0.6)',
                    borderColor: BLUE,
                    borderWidth: 1,
                    borderRadius: 3,
                    minBarLength: 40,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                onHover: (e, elements) => {
                    e.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                },
                onClick: (e, elements) => {
                    if (!elements.length) return;
                    const id = data[elements[0].index].id;
                    window.location.href = '{{ route('tv.show', ['series' => '__id__']) }}'.replace('__id__', id);
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const item = data[ctx.dataIndex];
                                return `${ctx.raw} days — ${item.episodes} eps — ${item.eps_per_day} eps/day`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: TICK, callback: v => v + 'd' },
                        grid: { color: GRID },
                    },
                    y: { ticks: { color: TICK, font: { size: 10 } }, grid: { display: false } },
                },
            },
        });
    })();
})();
</script>

<style>
.chart-period-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 0.375rem;
    color: #9ca3af;
    cursor: pointer;
    font-size: 0.8125rem;
    padding: 0.3rem 0.75rem;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.chart-period-btn:hover {
    background: rgba(250, 112, 154, 0.2);
    border-color: #fa709a;
    color: #fa709a;
}

.chart-period-btn.active {
    background: rgba(250, 112, 154, 0.25);
    border-color: #fa709a;
    color: #fa709a;
    font-weight: 600;
}

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
