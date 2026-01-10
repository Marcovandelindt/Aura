@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>PlayStation Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('playstation.index') }}">PlayStation</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('playstation.index') }}" class="btn btn-secondary">
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
            <form method="GET" action="{{ route('playstation.stats') }}" style="margin-bottom: 1.5rem;">
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
                        <div style="font-size: 1.5rem; font-weight: bold; color: #667eea;">{{ number_format($totalHours, 1) }}h</div>
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
                                <div style="font-weight: 600; color: #667eea;">
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #003087 0%, #0070cc 100%);">
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

    <!-- Earliest & Latest Session -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
        <!-- Earliest Session -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #F2994A 0%, #F2C94C 100%);">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="stat-details">
                        @if($stats['earliest_session'])
                            <h3 class="stat-value">{{ $stats['earliest_session']['time'] }}</h3>
                            <p class="stat-label">Earliest Session</p>
                            <small style="color: #666;">{{ $stats['earliest_session']['game_name'] }}</small>
                            <br><small style="color: #999;">{{ $stats['earliest_session']['day_name'] }}, {{ $stats['earliest_session']['date']->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Earliest Session</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Session -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2C3E50 0%, #4CA1AF 100%);">
                        <i class="fas fa-moon"></i>
                    </div>
                    <div class="stat-details">
                        @if($stats['latest_session'])
                            <h3 class="stat-value">{{ $stats['latest_session']['time'] }}</h3>
                            <p class="stat-label">Latest Session</p>
                            <small style="color: #666;">{{ $stats['latest_session']['game_name'] }}</small>
                            <br><small style="color: #999;">{{ $stats['latest_session']['day_name'] }}, {{ $stats['latest_session']['date']->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Latest Session</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Listening Times -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-clock" style="margin-right: 8px;"></i> Top Playing Times</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">When you play games most</p>
        </div>
        <div class="card-body">
            @if(count($stats['top_start_hours']) > 0)
                <div class="listening-times-grid">
                    @foreach($stats['top_start_hours'] as $index => $timeStats)
                        <div class="time-stat-card">
                            <div class="time-rank">{{ $index + 1 }}</div>
                            <div class="time-info">
                                <div class="time-label">{{ $timeStats['time_range'] }}</div>
                                <div class="time-description">{{ $timeStats['description'] }}</div>
                                <div class="time-count">{{ number_format($timeStats['count']) }} sessions</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">Not enough data yet to determine playing patterns.</p>
            @endif
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

        <!-- Late Night Sessions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-moon" style="margin-right: 8px;"></i> Late Night Gaming</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Sessions between 00:00 - 06:00</p>
            </div>
            <div class="card-body">
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #667eea;">
                        {{ $stats['late_night_sessions']['count'] }}
                    </div>
                    <div style="color: #666; margin-bottom: 1rem;">
                        {{ $stats['late_night_sessions']['percentage'] }}% of all sessions
                    </div>
                    @if($stats['late_night_sessions']['top_game'])
                        <div style="font-size: 0.875rem; color: #999;">
                            Most played at night: <strong>{{ $stats['late_night_sessions']['top_game'] }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Platform Stats -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-layer-group" style="margin-right: 8px;"></i> Platform Distribution</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Playtime by platform</p>
            </div>
            <div class="card-body">
                @if(count($stats['platform_stats']) > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($stats['platform_stats'] as $platform)
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span class="badge" style="background: {{ match($platform['platform']) {
                                    'PS5' => '#003087',
                                    'PS4' => '#00439c',
                                    'PS3' => '#006cb7',
                                    'PSVITA' => '#00acee',
                                    default => '#666'
                                } }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; min-width: 60px; text-align: center;">
                                    {{ $platform['platform'] }}
                                </span>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">{{ number_format($platform['hours'], 1) }}h</div>
                                    <div style="font-size: 0.75rem; color: #666;">{{ $platform['games'] }} games</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No platform data available.</p>
                @endif
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
                            <div style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 4px 4px 0 0; height: {{ $maxHours > 0 ? ($day['total_hours'] / $maxHours) * 100 : 0 }}px; min-height: 4px;"></div>
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
                                <div style="font-weight: 600; color: #667eea;">{{ number_format($year['hours'], 1) }}h</div>
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
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Last 12 months</p>
            </div>
            <div class="card-body">
                @if(count($stats['monthly_stats']) > 0)
                    <div class="top-list" style="max-height: 300px; overflow-y: auto;">
                        @foreach($stats['monthly_stats'] as $month)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $month['month_name'] }}</div>
                                    <div class="item-details" style="font-size: 0.75rem; color: #666;">
                                        {{ $month['sessions'] }} sessions
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: #667eea;">{{ $month['hours'] }}h</div>
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
                                        <div class="cover-placeholder">
                                            <i class="fas fa-gamepad"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">
                                        {{ $game['platform'] }} &bull; {{ $game['sessions'] }} sessions
                                        @if($game['completion_percentage'])
                                            &bull; {{ $game['completion_percentage'] }}%
                                        @endif
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
                                        {{ $game['platform'] }} &bull; {{ number_format($game['hours'], 1) }}h played
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

    <!-- Money & Value Statistics -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Money Overview -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-euro-sign" style="margin-right: 8px;"></i> Money Statistics</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your gaming investment</p>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 1.75rem; font-weight: bold; color: #10b981;">&euro;{{ number_format($stats['money_stats']['total_spent'], 2, ',', '.') }}</div>
                        <div style="font-size: 0.875rem; color: #666;">Total Spent</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 1.75rem; font-weight: bold; color: #667eea;">&euro;{{ number_format($stats['money_stats']['avg_price'], 2, ',', '.') }}</div>
                        <div style="font-size: 0.875rem; color: #666;">Avg Price per Game</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 1.75rem; font-weight: bold; color: #f5576c;">&euro;{{ number_format($stats['money_stats']['cost_per_hour'], 2, ',', '.') }}</div>
                        <div style="font-size: 0.875rem; color: #666;">Cost per Hour</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 1.75rem; font-weight: bold; color: #764ba2;">{{ $stats['money_stats']['games_with_price'] }}</div>
                        <div style="font-size: 0.875rem; color: #666;">Games with Price</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Value Games -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-gem" style="margin-right: 8px;"></i> Best Value Games</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Lowest cost per hour</p>
            </div>
            <div class="card-body">
                @if(count($stats['best_value_games']) > 0)
                    <div class="top-list">
                        @foreach($stats['best_value_games'] as $index => $game)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">
                                        {{ $game['platform'] }} &bull; &euro;{{ number_format($game['price'], 2, ',', '.') }} &bull; {{ $game['hours'] }}h
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 600; color: #10b981;">&euro;{{ number_format($game['cost_per_hour'], 2, ',', '.') }}/h</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No games with price data.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Worst Value & Genre Stats -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Worst Value Games (Shame Pile) -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-money-bill-wave" style="margin-right: 8px;"></i> Shame Pile</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Expensive games with < 1h played</p>
            </div>
            <div class="card-body">
                @if(count($stats['worst_value_games']) > 0)
                    <div class="top-list">
                        @foreach($stats['worst_value_games'] as $game)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">
                                        {{ $game['platform'] }} &bull; {{ $game['hours'] }}h played
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 600; color: #f56565;">&euro;{{ number_format($game['price'], 2, ',', '.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No wasted money! All games are being played.</p>
                @endif
            </div>
        </div>

        <!-- Genre Statistics -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-tags" style="margin-right: 8px;"></i> Favorite Genres</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Most played genres by hours</p>
            </div>
            <div class="card-body">
                @if(count($stats['genre_stats']) > 0)
                    @php $maxGenreHours = $stats['genre_stats'][0]['hours'] ?? 1; @endphp
                    <div class="top-list">
                        @foreach(array_slice($stats['genre_stats'], 0, 8) as $index => $genre)
                            <div style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 500;">{{ $genre['name'] }}</span>
                                    <span style="color: #667eea; font-weight: 600;">{{ $genre['hours'] }}h</span>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden;">
                                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; width: {{ ($genre['hours'] / $maxGenreHours) * 100 }}%;"></div>
                                </div>
                                <div style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                    {{ $genre['game_count'] }} games &bull; {{ $genre['sessions'] }} sessions
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No genre data available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Session Length Distribution -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="margin-right: 8px;"></i> Session Length Distribution</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">How long are your gaming sessions?</p>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem;">
                @foreach($stats['session_length_distribution'] as $category)
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #667eea;">{{ $category['count'] }}</div>
                        <div style="font-size: 0.875rem; font-weight: 500;">{{ $category['label'] }}</div>
                        <div style="font-size: 0.75rem; color: #666;">{{ $category['percentage'] }}%</div>
                        <div style="font-size: 0.7rem; color: #999;">{{ $category['hours'] }}h</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Marathon & Micro Sessions -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Marathon Sessions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-running" style="margin-right: 8px;"></i> Marathon Sessions</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Sessions over 4 hours</p>
            </div>
            <div class="card-body">
                @if(count($stats['marathon_sessions']) > 0)
                    <div class="top-list">
                        @foreach($stats['marathon_sessions'] as $session)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $session['game_name'] }}</div>
                                    <div class="item-details">
                                        {{ $session['platform'] }} &bull; {{ $session['day_name'] }}, {{ $session['date']->format('d M Y') }}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 600; color: #f5576c;">{{ $session['hours'] }}h {{ $session['minutes'] }}m</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No marathon sessions yet. Time to game harder!</p>
                @endif
            </div>
        </div>

        <!-- Micro Sessions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bolt" style="margin-right: 8px;"></i> Quick Sessions</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Sessions under 15 minutes</p>
            </div>
            <div class="card-body">
                <div style="text-align: center; padding: 1.5rem 0;">
                    <div style="font-size: 3rem; font-weight: bold; color: #667eea;">{{ $stats['micro_sessions']['count'] }}</div>
                    <div style="font-size: 1rem; color: #666; margin-bottom: 0.5rem;">{{ $stats['micro_sessions']['percentage'] }}% of all sessions</div>
                    @if($stats['micro_sessions']['top_game'])
                        <div style="font-size: 0.875rem; color: #999;">
                            Most common: <strong>{{ $stats['micro_sessions']['top_game'] }}</strong>
                            ({{ $stats['micro_sessions']['top_game_count'] }}x)
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gaming Heatmap -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-th" style="margin-right: 8px;"></i> Gaming Heatmap</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">When do you play the most?</p>
        </div>
        <div class="card-body">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.7rem;">
                    <thead>
                        <tr>
                            <th style="padding: 4px; text-align: left;"></th>
                            @for($hour = 0; $hour < 24; $hour++)
                                <th style="padding: 4px; text-align: center; color: #666;">{{ sprintf('%02d', $hour) }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['gaming_heatmap']['days'] as $day)
                            <tr>
                                <td style="padding: 4px; font-weight: 500;">{{ $day }}</td>
                                @for($hour = 0; $hour < 24; $hour++)
                                    @php
                                        $count = $stats['gaming_heatmap']['data'][$day][$hour] ?? 0;
                                        $maxCount = $stats['gaming_heatmap']['max_count'] ?: 1;
                                        $intensity = $count / $maxCount;
                                        $bgColor = $count > 0 ? "rgba(102, 126, 234, " . (0.2 + ($intensity * 0.8)) . ")" : "#f3f4f6";
                                    @endphp
                                    <td style="padding: 4px; text-align: center; background: {{ $bgColor }}; border-radius: 2px;" title="{{ $day }} {{ sprintf('%02d:00', $hour) }}: {{ $count }} sessions">
                                        @if($count > 0)
                                            <span style="color: {{ $intensity > 0.5 ? 'white' : '#333' }};">{{ $count }}</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Records & Milestones -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Biggest Gaming Day -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-crown" style="margin-right: 8px;"></i> Biggest Gaming Day</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your most intense gaming day ever</p>
            </div>
            <div class="card-body">
                @if($stats['biggest_gaming_day'])
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: #f5576c;">{{ $stats['biggest_gaming_day']['hours'] }}h</div>
                        <div style="font-size: 1rem; color: #666;">{{ $stats['biggest_gaming_day']['date']->format('l, d F Y') }}</div>
                        <div style="font-size: 0.875rem; color: #999; margin-top: 0.5rem;">{{ $stats['biggest_gaming_day']['sessions'] }} sessions</div>
                        @if(count($stats['biggest_gaming_day']['games']) > 0)
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                @foreach($stats['biggest_gaming_day']['games'] as $game)
                                    <span style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                        {{ $game['name'] }} ({{ round($game['minutes'] / 60, 1) }}h)
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <p class="no-data">No gaming data yet.</p>
                @endif
            </div>
        </div>

        <!-- Most Active Month -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-check" style="margin-right: 8px;"></i> Most Active Month</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your peak gaming month</p>
            </div>
            <div class="card-body">
                @if($stats['most_active_month'])
                    <div style="text-align: center; padding: 1rem 0;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: #667eea;">{{ $stats['most_active_month']['hours'] }}h</div>
                        <div style="font-size: 1.25rem; color: #333;">{{ $stats['most_active_month']['month_name'] }}</div>
                        <div style="font-size: 0.875rem; color: #999; margin-top: 0.5rem;">{{ $stats['most_active_month']['sessions'] }} sessions</div>
                    </div>
                @else
                    <p class="no-data">No monthly data yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Month Comparison & Activity -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Month Comparison -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exchange-alt" style="margin-right: 8px;"></i> Month Comparison</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">This month vs last month</p>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-around; align-items: center; padding: 1rem 0;">
                    <div style="text-align: center;">
                        <div style="font-size: 0.875rem; color: #666;">{{ $stats['month_comparison']['last_month']['name'] }}</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #999;">{{ $stats['month_comparison']['last_month']['hours'] }}h</div>
                        <div style="font-size: 0.75rem; color: #999;">{{ $stats['month_comparison']['last_month']['sessions'] }} sessions</div>
                    </div>
                    <div style="text-align: center;">
                        @if($stats['month_comparison']['trend'] === 'up')
                            <i class="fas fa-arrow-up" style="font-size: 2rem; color: #10b981;"></i>
                            <div style="font-size: 0.875rem; color: #10b981;">+{{ $stats['month_comparison']['percent_change'] }}%</div>
                        @elseif($stats['month_comparison']['trend'] === 'down')
                            <i class="fas fa-arrow-down" style="font-size: 2rem; color: #f56565;"></i>
                            <div style="font-size: 0.875rem; color: #f56565;">{{ $stats['month_comparison']['percent_change'] }}%</div>
                        @else
                            <i class="fas fa-equals" style="font-size: 2rem; color: #666;"></i>
                            <div style="font-size: 0.875rem; color: #666;">Same</div>
                        @endif
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 0.875rem; color: #666;">{{ $stats['month_comparison']['this_month']['name'] }}</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #667eea;">{{ $stats['month_comparison']['this_month']['hours'] }}h</div>
                        <div style="font-size: 0.75rem; color: #999;">{{ $stats['month_comparison']['this_month']['sessions'] }} sessions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Days Since Last Session & Avg Games -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-hourglass-half" style="margin-right: 8px;"></i> Activity Status</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your gaming activity</p>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: bold; color: {{ $stats['days_since_last_session'] > 7 ? '#f56565' : '#10b981' }};">
                            {{ $stats['days_since_last_session'] }}
                        </div>
                        <div style="font-size: 0.875rem; color: #666;">Days Since Last Session</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: bold; color: #667eea;">{{ $stats['avg_games_per_month'] }}</div>
                        <div style="font-size: 0.875rem; color: #666;">Avg Games per Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seasonal Patterns -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-snowflake" style="margin-right: 8px;"></i> Seasonal Patterns</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">When do you game most throughout the year?</p>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                @php
                    $seasonIcons = ['Winter' => 'snowflake', 'Spring' => 'seedling', 'Summer' => 'sun', 'Autumn' => 'leaf'];
                    $seasonColors = ['Winter' => '#60a5fa', 'Spring' => '#34d399', 'Summer' => '#fbbf24', 'Autumn' => '#f97316'];
                @endphp
                @foreach($stats['seasonal_patterns']['seasons'] as $season => $data)
                    <div style="text-align: center; padding: 1.5rem; background: {{ $season === $stats['seasonal_patterns']['favorite_season'] ? 'linear-gradient(135deg, ' . $seasonColors[$season] . '22 0%, ' . $seasonColors[$season] . '11 100%)' : '#f8f9fa' }}; border-radius: 8px; {{ $season === $stats['seasonal_patterns']['favorite_season'] ? 'border: 2px solid ' . $seasonColors[$season] : '' }}">
                        <i class="fas fa-{{ $seasonIcons[$season] }}" style="font-size: 2rem; color: {{ $seasonColors[$season] }}; margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 1rem; font-weight: 600;">{{ $season }}</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: {{ $seasonColors[$season] }};">{{ $data['hours'] }}h</div>
                        <div style="font-size: 0.75rem; color: #666;">{{ $data['sessions'] }} sessions</div>
                        @if($season === $stats['seasonal_patterns']['favorite_season'])
                            <div style="margin-top: 0.5rem; font-size: 0.7rem; background: {{ $seasonColors[$season] }}; color: white; padding: 2px 8px; border-radius: 10px; display: inline-block;">Favorite</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Fun Stats & Trivia -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Game Hopping Rate -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-random" style="margin-right: 8px;"></i> Game Hopping Rate</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">How often you switch games</p>
            </div>
            <div class="card-body">
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="font-size: 3rem; font-weight: bold; color: #667eea;">{{ $stats['game_hopping_rate']['rate'] }}%</div>
                    <div style="font-size: 1.25rem; font-weight: 500; color: #333; margin: 0.5rem 0;">{{ $stats['game_hopping_rate']['player_type'] }}</div>
                    <div style="font-size: 0.875rem; color: #666;">
                        {{ $stats['game_hopping_rate']['game_changes'] }} game switches in {{ $stats['game_hopping_rate']['total_sessions'] }} sessions
                    </div>
                </div>
            </div>
        </div>

        <!-- First Game Ever -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-baby" style="margin-right: 8px;"></i> First Game Played</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Where it all began</p>
            </div>
            <div class="card-body">
                @if($stats['first_game_played'])
                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem 0;">
                        @if($stats['first_game_played']['image_url'])
                            <img src="{{ $stats['first_game_played']['image_url'] }}" alt="{{ $stats['first_game_played']['name'] }}" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                        @else
                            <div style="width: 60px; height: 60px; background: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-gamepad" style="color: #999;"></i>
                            </div>
                        @endif
                        <div>
                            <div style="font-weight: 600; font-size: 1.1rem;">{{ $stats['first_game_played']['name'] }}</div>
                            <div style="color: #666; font-size: 0.875rem;">{{ $stats['first_game_played']['platform'] }}</div>
                            <div style="color: #999; font-size: 0.75rem;">{{ $stats['first_game_played']['date']->format('d F Y') }}</div>
                        </div>
                    </div>
                @else
                    <p class="no-data">No games played yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- One Hit Wonders & Loyal Games -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- One Hit Wonders -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-dice-one" style="margin-right: 8px;"></i> One-Hit Wonders</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Games played only once</p>
            </div>
            <div class="card-body">
                @if(count($stats['one_hit_wonders']) > 0)
                    <div class="top-list">
                        @foreach($stats['one_hit_wonders'] as $game)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">{{ $game['platform'] }}</div>
                                </div>
                                @if($game['last_played_at'])
                                    <div style="font-size: 0.75rem; color: #999;">{{ $game['last_played_at']->format('d M Y') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No one-hit wonders found.</p>
                @endif
            </div>
        </div>

        <!-- Loyal Games -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-heart" style="margin-right: 8px;"></i> Loyal Games</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Games played across multiple years</p>
            </div>
            <div class="card-body">
                @if(count($stats['loyal_games']) > 0)
                    <div class="top-list">
                        @foreach($stats['loyal_games'] as $game)
                            <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div class="item-info">
                                    <div class="item-name">{{ $game['name'] }}</div>
                                    <div class="item-details">
                                        {{ $game['platform'] }} &bull; {{ $game['sessions'] }} sessions
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 600; color: #f56565;">{{ $game['years_played'] }} years</div>
                                    <div style="font-size: 0.7rem; color: #999;">{{ implode(', ', $game['years']) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No loyal games yet. Keep playing!</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Library Growth -->
    @if(count($stats['library_growth']) > 0)
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-area" style="margin-right: 8px;"></i> Library Growth</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Games added per month</p>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 0.25rem; align-items: flex-end; height: 100px; overflow-x: auto; padding-bottom: 0.5rem;">
                @php
                    $maxGrowth = max(array_column($stats['library_growth'], 'count'));
                @endphp
                @foreach($stats['library_growth'] as $month)
                    <div style="flex: 0 0 40px; display: flex; flex-direction: column; align-items: center;">
                        <div style="width: 30px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 4px 4px 0 0; height: {{ $maxGrowth > 0 ? ($month['count'] / $maxGrowth) * 80 : 0 }}px; min-height: 4px;"></div>
                        <div style="font-size: 0.6rem; color: #666; margin-top: 4px; white-space: nowrap; transform: rotate(-45deg); transform-origin: top left; width: 50px;">{{ $month['month_name'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
