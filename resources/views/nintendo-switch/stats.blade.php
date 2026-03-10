@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Nintendo Switch Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('nintendo-switch.index') }}">Nintendo Switch</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('nintendo-switch.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Games
                </a>
            </div>
        </div>
    </div>

    <!-- Daily Playtime Filter -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-calendar-day" style="margin-right: 8px;"></i> Daily Playtime</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Filter to see playtime per day</p>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('nintendo-switch.stats') }}" style="margin-bottom: 1.5rem;">
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Period</label>
                        <select name="filter" onchange="toggleDateInputs(this.value)" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; min-width: 150px;">
                            <option value="7" {{ ($filterData['current'] ?? '30') === '7' ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ ($filterData['current'] ?? '30') === '30' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ ($filterData['current'] ?? '30') === '90' ? 'selected' : '' }}>Last 90 days</option>
                            <option value="365" {{ ($filterData['current'] ?? '30') === '365' ? 'selected' : '' }}>Last year</option>
                            <option value="all" {{ ($filterData['current'] ?? '30') === 'all' ? 'selected' : '' }}>All time</option>
                            <option value="single" {{ ($filterData['current'] ?? '30') === 'single' ? 'selected' : '' }}>Single day</option>
                            <option value="custom" {{ ($filterData['current'] ?? '30') === 'custom' ? 'selected' : '' }}>Custom range</option>
                        </select>
                    </div>
                    <div id="singleDate" style="display: {{ ($filterData['current'] ?? '30') === 'single' ? 'block' : 'none' }};">
                        <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Date</label>
                        <input type="date" id="singleDateInput" name="start_date" value="{{ ($filterData['current'] ?? '30') === 'single' ? ($filterData['start_date'] ?? '') : '' }}" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;" {{ ($filterData['current'] ?? '30') !== 'single' ? 'disabled' : '' }}>
                    </div>
                    <div id="customDates" style="display: {{ ($filterData['current'] ?? '30') === 'custom' ? 'flex' : 'none' }}; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">From</label>
                            <input type="date" id="customStartDate" name="start_date" value="{{ ($filterData['current'] ?? '30') === 'custom' ? ($filterData['start_date'] ?? '') : '' }}" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;" {{ ($filterData['current'] ?? '30') !== 'custom' ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">To</label>
                            <input type="date" id="customEndDate" name="end_date" value="{{ ($filterData['current'] ?? '30') === 'custom' ? ($filterData['end_date'] ?? '') : '' }}" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;" {{ ($filterData['current'] ?? '30') !== 'custom' ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>

            @if(count($stats['daily_playtime']) > 0)
                @php
                    $totalHours = array_sum(array_column($stats['daily_playtime'], 'hours'));
                    $totalSessions = array_sum(array_column($stats['daily_playtime'], 'sessions'));
                    $avgHoursPerDay = count($stats['daily_playtime']) > 0 ? round($totalHours / count($stats['daily_playtime']), 1) : 0;
                @endphp
                <div style="display: flex; gap: 2rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #e60012;">{{ number_format($totalHours, 1) }}h</div>
                        <div style="font-size: 0.875rem; color: #666;">Total in period</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #764ba2;">{{ $totalSessions }}</div>
                        <div style="font-size: 0.875rem; color: #666;">Sessions</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #f5576c;">{{ $avgHoursPerDay }}h</div>
                        <div style="font-size: 0.875rem; color: #666;">Avg per day</div>
                    </div>
                </div>

                <div class="top-list" style="max-height: 500px; overflow-y: auto;">
                    @foreach($stats['daily_playtime'] as $day)
                        <div style="padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div>
                                    <div class="item-name">{{ $day['date_formatted'] }}</div>
                                    <div class="item-details" style="font-size: 0.875rem; color: #666;">
                                        {{ $day['sessions'] }} {{ $day['sessions'] === 1 ? 'session' : 'sessions' }}
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: #e60012;">
                                    @if($day['hours'] >= 1)
                                        {{ $day['hours'] }}h
                                    @else
                                        {{ $day['minutes'] }}m
                                    @endif
                                </div>
                            </div>
                            @if(count($day['games']) > 0)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-left: 0.5rem;">
                                    @foreach($day['games'] as $game)
                                        <span style="font-size: 0.75rem; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px; color: #4b5563;">
                                            {{ $game['name'] }} <span style="color: #9ca3af;">({{ $game['hours'] >= 1 ? $game['hours'] . 'h' : $game['minutes'] . 'm' }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">No playtime data found for this period.</p>
            @endif
        </div>
    </div>

    <script>
        function toggleDateInputs(value) {
            const singleDate = document.getElementById('singleDate');
            const customDates = document.getElementById('customDates');
            const singleInput = document.getElementById('singleDateInput');
            const customStart = document.getElementById('customStartDate');
            const customEnd = document.getElementById('customEndDate');

            singleDate.style.display = value === 'single' ? 'block' : 'none';
            customDates.style.display = value === 'custom' ? 'flex' : 'none';

            singleInput.disabled = value !== 'single';
            customStart.disabled = value !== 'custom';
            customEnd.disabled = value !== 'custom';
        }
    </script>

    <!-- Overview Statistics -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e60012 0%, #ff4444 100%);">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_sessions']) }}</h3>
                        <p class="stat-label">Total Sessions</p>
                        <span class="stat-change">{{ $stats['overview']['avg_sessions_per_week'] }} per week</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['overview']['total_hours'], 1) }}h</h3>
                        <p class="stat-label">Total Playtime</p>
                        <span class="stat-change">{{ $stats['overview']['avg_hours_per_week'] }}h per week</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['overview']['sessions_this_week'] }}</h3>
                        <p class="stat-label">This Week</p>
                        <span class="stat-change">{{ $stats['overview']['sessions_this_month'] }} this month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['overview']['unique_games_played'] }}</h3>
                        <p class="stat-label">Games Played</p>
                        <span class="stat-change">{{ $stats['overview']['total_games'] }} total games</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Records -->
    <div class="stats-grid" style="margin-top: 2rem;">
        <!-- Longest Session -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="stat-details">
                        @if($stats['longest_session'])
                            <h3 class="stat-value">{{ $stats['longest_session']['hours'] }}h {{ $stats['longest_session']['minutes'] }}m</h3>
                            <p class="stat-label">Longest Session</p>
                            <small style="color: #666;">{{ $stats['longest_session']['game_name'] }}</small>
                            <br><small style="color: #999;">{{ $stats['longest_session']['date']->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Longest Session</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortest Session -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="stat-details">
                        @if($stats['shortest_session'])
                            <h3 class="stat-value">{{ $stats['shortest_session']['duration_minutes'] }}m</h3>
                            <p class="stat-label">Shortest Session</p>
                            <small style="color: #666;">{{ $stats['shortest_session']['game_name'] }}</small>
                            <br><small style="color: #999;">{{ $stats['shortest_session']['date']->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Shortest Session</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Streak -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f857a6 0%, #ff5858 100%);">
                        <i class="fas fa-fire-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['current_streak']['days'] }} days</h3>
                        <p class="stat-label">Current Streak</p>
                        @if($stats['current_streak']['start_date'])
                            <small style="color: #666;">Since {{ \Carbon\Carbon::parse($stats['current_streak']['start_date'])->format('d M') }}</small>
                        @else
                            <small style="color: #666;">No active streak</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Longest Streak -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%);">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-details">
                        @if($stats['longest_streak']['days'] > 0)
                            <h3 class="stat-value">{{ $stats['longest_streak']['days'] }} days</h3>
                            <p class="stat-label">Longest Streak</p>
                            <small style="color: #666;">{{ $stats['longest_streak']['game']?->name ?? 'Unknown' }}</small>
                            <br><small style="color: #999;">{{ \Carbon\Carbon::parse($stats['longest_streak']['start_date'])->format('d M') }} - {{ \Carbon\Carbon::parse($stats['longest_streak']['end_date'])->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Longest Streak</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Statistics Grid -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Weekday vs Weekend -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar" style="margin-right: 8px;"></i> Weekday vs Weekend</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your gaming patterns</p>
            </div>
            <div class="card-body">
                <div class="weekday-weekend-chart">
                    <div class="chart-item">
                        <div class="chart-bar weekday-bar" style="height: {{ $stats['weekday_vs_weekend']['weekday_percentage'] }}%;">
                            <span class="chart-value">{{ $stats['weekday_vs_weekend']['weekday_percentage'] }}%</span>
                        </div>
                        <div class="chart-label">
                            <strong>Weekdays</strong>
                            <span>{{ number_format($stats['weekday_vs_weekend']['weekday_hours'], 1) }}h</span>
                        </div>
                    </div>
                    <div class="chart-item">
                        <div class="chart-bar weekend-bar" style="height: {{ $stats['weekday_vs_weekend']['weekend_percentage'] }}%;">
                            <span class="chart-value">{{ $stats['weekday_vs_weekend']['weekend_percentage'] }}%</span>
                        </div>
                        <div class="chart-label">
                            <strong>Weekend</strong>
                            <span>{{ number_format($stats['weekday_vs_weekend']['weekend_hours'], 1) }}h</span>
                        </div>
                    </div>
                </div>
                <div class="preference-indicator">
                    @if($stats['weekday_vs_weekend']['preference'] === 'weekday')
                        <span class="preference weekday">You're a <strong>weekday gamer</strong></span>
                    @elseif($stats['weekday_vs_weekend']['preference'] === 'weekend')
                        <span class="preference weekend">You're a <strong>weekend warrior</strong></span>
                    @else
                        <span class="preference equal"><strong>Perfectly balanced</strong></span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Playtime by Day Charts -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
        <!-- Total Playtime by Day -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar" style="margin-right: 8px;"></i> Total Playtime by Day</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">All sessions combined per weekday</p>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 0.5rem; align-items: flex-end; height: 120px;">
                    @php
                        $maxHours = max(array_column($stats['avg_session_by_day'], 'total_hours'));
                    @endphp
                    @foreach($stats['avg_session_by_day'] as $day)
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 100%; background: linear-gradient(135deg, #e60012 0%, #ff4444 100%); border-radius: 4px 4px 0 0; height: {{ $maxHours > 0 ? ($day['total_hours'] / $maxHours) * 100 : 0 }}px; min-height: 4px;"></div>
                            <div style="font-size: 0.7rem; color: #666; margin-top: 4px;">{{ $day['day_short'] }}</div>
                            <div style="font-size: 0.65rem; color: #999;">{{ $day['total_hours'] }}h</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Average Playtime by Day -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right: 8px;"></i> Avg Playtime by Day</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Average playtime when playing on that day</p>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 0.5rem; align-items: flex-end; height: 120px;">
                    @php
                        $maxAvgHours = max(array_column($stats['avg_session_by_day'], 'avg_hours'));
                    @endphp
                    @foreach($stats['avg_session_by_day'] as $day)
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="width: 100%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 4px 4px 0 0; height: {{ $maxAvgHours > 0 ? ($day['avg_hours'] / $maxAvgHours) * 100 : 0 }}px; min-height: 4px;"></div>
                            <div style="font-size: 0.7rem; color: #666; margin-top: 4px;">{{ $day['day_short'] }}</div>
                            <div style="font-size: 0.65rem; color: #999;">{{ $day['avg_hours'] }}h</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Yearly Comparison & Monthly Stats -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Yearly Comparison -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i> Yearly Comparison</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Playtime per year</p>
            </div>
            <div class="card-body">
                @if(count($stats['yearly_comparison']) > 0)
                    <div class="top-list">
                        @foreach($stats['yearly_comparison'] as $year)
                            <div class="top-list-item" style="padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name" style="font-weight: 600;">{{ $year['year'] }}</div>
                                    <div class="item-details" style="font-size: 0.875rem; color: #666;">
                                        {{ number_format($year['sessions']) }} sessions
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: #e60012;">{{ number_format($year['hours'], 1) }}h</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No yearly data available.</p>
                @endif
            </div>
        </div>

        <!-- Monthly Stats -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right: 8px;"></i> Monthly Activity</h3>
            </div>
            <div class="card-body">
                @if(count($stats['monthly_stats']) > 0)
                    <div class="top-list" style="max-height: 1000px; overflow-y: auto;">
                        @foreach($stats['monthly_stats'] as $month)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $month['month_name'] }}</div>
                                    <div class="item-details" style="font-size: 0.75rem; color: #666;">
                                        {{ $month['sessions'] }} sessions
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: #e60012;">{{ $month['hours'] }}h</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No monthly data available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Games & Abandoned Games -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Top Games -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-star" style="margin-right: 8px;"></i> Top 10 Games</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Most played games</p>
            </div>
            <div class="card-body">
                @if(count($stats['top_games']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_games'] as $index => $game)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="track-cover">
                                    @if($game['image_url'])
                                        <img src="{{ $game['image_url'] }}" alt="{{ $game['name'] }}" class="cover-image" style="border-radius: 4px;">
                                    @else
                                        <div class="cover-placeholder" style="background: #e60012;">
                                            <i class="fas fa-gamepad" style="color: white;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">
                                        {{ $game['sessions'] }} sessions
                                    </div>
                                </div>
                                <div class="play-count">{{ number_format($game['hours'], 1) }}h</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No game data available.</p>
                @endif
            </div>
        </div>

        <!-- Abandoned Games -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-ghost" style="margin-right: 8px;"></i> Abandoned Games</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Games not played in 90+ days</p>
            </div>
            <div class="card-body">
                @if(count($stats['abandoned_games']) > 0)
                    <div class="top-list">
                        @foreach($stats['abandoned_games'] as $game)
                            <div class="top-list-item" style="padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">
                                        {{ number_format($game['hours'], 1) }}h played
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.875rem; color: #f56565;">{{ $game['days_since'] }} days ago</div>
                                    @if($game['last_played_at'])
                                        <div style="font-size: 0.75rem; color: #999;">{{ $game['last_played_at']->format('d M Y') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No abandoned games found. Keep gaming!</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
