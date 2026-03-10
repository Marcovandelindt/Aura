@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Health Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('health.index') }}">Health</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('health.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Health
                </a>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('health.stats') }}">
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Period</label>
                        <select name="filter" onchange="toggleDateInputs(this.value)" class="form-control" style="min-width: 150px;">
                            <option value="all" {{ ($filterData['current'] ?? 'all') === 'all' ? 'selected' : '' }}>All time</option>
                            <option value="7" {{ ($filterData['current'] ?? 'all') === '7' ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ ($filterData['current'] ?? 'all') === '30' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ ($filterData['current'] ?? 'all') === '90' ? 'selected' : '' }}>Last 90 days</option>
                            <option value="365" {{ ($filterData['current'] ?? 'all') === '365' ? 'selected' : '' }}>Last year</option>
                            <option value="custom" {{ ($filterData['current'] ?? 'all') === 'custom' ? 'selected' : '' }}>Custom range</option>
                        </select>
                    </div>
                    <div id="customDates" style="display: {{ ($filterData['current'] ?? 'all') === 'custom' ? 'flex' : 'none' }}; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">From</label>
                            <input type="date" id="customStartDate" name="start_date" value="{{ $filterData['start_date'] ?? '' }}" class="form-control">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">To</label>
                            <input type="date" id="customEndDate" name="end_date" value="{{ $filterData['end_date'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleDateInputs(value) {
            const customDates = document.getElementById('customDates');
            customDates.style.display = value === 'custom' ? 'flex' : 'none';
        }
    </script>

    <!-- Overview Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon health-accent">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($stats['overview']['total_entries']) }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon health-accent">
                <i class="fas fa-shoe-prints"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['total_steps'] ? number_format($stats['overview']['total_steps']) : '-' }}</div>
                <div class="stat-label">Total Steps</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon health-accent">
                <i class="fas fa-route"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['total_km'] ? number_format($stats['overview']['total_km'], 1) : '-' }} <span style="font-size: 0.875rem; font-weight: 400;">km</span></div>
                <div class="stat-label">Total Distance</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon health-accent">
                <i class="fas fa-walking"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['avg_steps'] ? number_format($stats['overview']['avg_steps']) : '-' }}</div>
                <div class="stat-label">Avg Steps/Day</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['avg_heart_rate'] ?? '-' }}</div>
                <div class="stat-label">Avg Heart Rate</div>
            </div>
        </div>
    </div>

    <!-- Streaks & Goals Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Streaks Card -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-fire" style="margin-right: 8px; color: #f97316;"></i> Streaks</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Current Streak</span>
                        <span class="streak-badge {{ $stats['streaks']['current_streak'] > 0 ? 'active' : 'inactive' }}">
                            @if($stats['streaks']['current_streak'] > 0)
                                <span class="streak-fire">🔥</span>
                            @endif
                            {{ $stats['streaks']['current_streak'] }} days
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Longest Streak</span>
                        <span style="font-weight: 600; color: var(--primary-color);">{{ $stats['streaks']['longest_streak'] }} days</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Goal Streak ({{ number_format($stats['step_goals']['goal']) }}+ steps)</span>
                        <span class="streak-badge {{ $stats['streaks']['goal_streak'] > 0 ? 'active' : 'inactive' }}">
                            @if($stats['streaks']['goal_streak'] > 0)
                                <span class="streak-fire">⭐</span>
                            @endif
                            {{ $stats['streaks']['goal_streak'] }} days
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Goal Card -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bullseye" style="margin-right: 8px; color: #10b981;"></i> Step Goal ({{ number_format($stats['step_goals']['goal']) }})</h3>
            </div>
            <div class="card-body">
                <div style="text-align: center; margin-bottom: 1rem;">
                    <div style="font-size: 3rem; font-weight: bold; color: #10b981;">{{ $stats['step_goals']['achievement_rate'] }}%</div>
                    <div style="color: #6b7280;">Achievement Rate</div>
                </div>
                <div class="goal-progress">
                    <div class="progress-bar" style="width: {{ $stats['step_goals']['achievement_rate'] }}%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 0.875rem; color: #6b7280;">
                    <span>{{ $stats['step_goals']['total_goals_met'] }} goals met</span>
                    <span>{{ $stats['step_goals']['total_entries_with_steps'] }} entries</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparisons Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Weekly Comparison -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-week" style="margin-right: 8px;"></i> Weekly Comparison</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">This Week</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #10b981;">
                            {{ $stats['weekly_comparison']['this_week']['avg_steps'] ? number_format($stats['weekly_comparison']['this_week']['avg_steps']) : '-' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">avg steps</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Last Week</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #6b7280;">
                            {{ $stats['weekly_comparison']['last_week']['avg_steps'] ? number_format($stats['weekly_comparison']['last_week']['avg_steps']) : '-' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">avg steps</div>
                    </div>
                </div>
                @if($stats['weekly_comparison']['steps_percent_change'] != 0)
                    <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <span style="color: {{ $stats['weekly_comparison']['trend'] === 'up' ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            <i class="fas fa-arrow-{{ $stats['weekly_comparison']['trend'] }}"></i>
                            {{ abs($stats['weekly_comparison']['steps_percent_change']) }}%
                        </span>
                        <span style="color: #6b7280; font-size: 0.875rem;">vs last week</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i> Monthly Comparison</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">{{ $stats['monthly_comparison']['this_month']['name'] }}</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #10b981;">
                            {{ $stats['monthly_comparison']['this_month']['avg_steps'] ? number_format($stats['monthly_comparison']['this_month']['avg_steps']) : '-' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">avg steps</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">{{ $stats['monthly_comparison']['last_month']['name'] }}</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #6b7280;">
                            {{ $stats['monthly_comparison']['last_month']['avg_steps'] ? number_format($stats['monthly_comparison']['last_month']['avg_steps']) : '-' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">avg steps</div>
                    </div>
                </div>
                @if($stats['monthly_comparison']['steps_percent_change'] != 0)
                    <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <span style="color: {{ $stats['monthly_comparison']['trend'] === 'up' ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            <i class="fas fa-arrow-{{ $stats['monthly_comparison']['trend'] }}"></i>
                            {{ abs($stats['monthly_comparison']['steps_percent_change']) }}%
                        </span>
                        <span style="color: #6b7280; font-size: 0.875rem;">vs last month</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Best Days & Heart Rate Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Best Days -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-trophy" style="margin-right: 8px; color: #f59e0b;"></i> Personal Records</h3>
            </div>
            <div class="card-body">
                @if($stats['best_days']['most_steps'])
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Most Steps</div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 1.5rem; font-weight: bold; color: #10b981;">
                                {{ number_format($stats['best_days']['most_steps']['steps']) }}
                            </span>
                            <span style="font-size: 0.875rem; color: #6b7280;">
                                {{ $stats['best_days']['most_steps']['date']->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                @endif
                @if($stats['best_days']['lowest_resting_hr'])
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Lowest Resting HR</div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 1.5rem; font-weight: bold; color: #f59e0b;">
                                {{ $stats['best_days']['lowest_resting_hr']['resting_heart_rate'] }} bpm
                            </span>
                            <span style="font-size: 0.875rem; color: #6b7280;">
                                {{ $stats['best_days']['lowest_resting_hr']['date']->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                @endif
                @if(!$stats['best_days']['most_steps'] && !$stats['best_days']['lowest_resting_hr'])
                    <p style="color: #6b7280; text-align: center;">No records yet. Start tracking!</p>
                @endif
            </div>
        </div>

        <!-- Heart Rate Stats -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-heartbeat" style="margin-right: 8px; color: #ef4444;"></i> Heart Rate Overview</h3>
            </div>
            <div class="card-body">
                @if($stats['heart_rate_stats']['avg_heart_rate']['count'] > 0)
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Average Heart Rate</div>
                        <div style="display: flex; gap: 1rem;">
                            <div style="text-align: center; flex: 1; padding: 0.5rem; background: var(--card-bg); border-radius: 8px;">
                                <div style="font-size: 0.75rem; color: #6b7280;">Min</div>
                                <div style="font-weight: 600; color: #3b82f6;">{{ $stats['heart_rate_stats']['avg_heart_rate']['min'] }}</div>
                            </div>
                            <div style="text-align: center; flex: 1; padding: 0.5rem; background: var(--card-bg); border-radius: 8px;">
                                <div style="font-size: 0.75rem; color: #6b7280;">Avg</div>
                                <div style="font-weight: 600; color: #ef4444;">{{ $stats['heart_rate_stats']['avg_heart_rate']['avg'] }}</div>
                            </div>
                            <div style="text-align: center; flex: 1; padding: 0.5rem; background: var(--card-bg); border-radius: 8px;">
                                <div style="font-size: 0.75rem; color: #6b7280;">Max</div>
                                <div style="font-weight: 600; color: #f97316;">{{ $stats['heart_rate_stats']['avg_heart_rate']['max'] }}</div>
                            </div>
                        </div>
                    </div>
                @endif
                @if($stats['heart_rate_stats']['resting_heart_rate']['count'] > 0)
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Resting Heart Rate</div>
                        <div style="display: flex; gap: 1rem;">
                            <div style="text-align: center; flex: 1; padding: 0.5rem; background: var(--card-bg); border-radius: 8px;">
                                <div style="font-size: 0.75rem; color: #6b7280;">Min</div>
                                <div style="font-weight: 600; color: #10b981;">{{ $stats['heart_rate_stats']['resting_heart_rate']['min'] }}</div>
                            </div>
                            <div style="text-align: center; flex: 1; padding: 0.5rem; background: var(--card-bg); border-radius: 8px;">
                                <div style="font-size: 0.75rem; color: #6b7280;">Avg</div>
                                <div style="font-weight: 600; color: #f59e0b;">{{ $stats['heart_rate_stats']['resting_heart_rate']['avg'] }}</div>
                            </div>
                            <div style="text-align: center; flex: 1; padding: 0.5rem; background: var(--card-bg); border-radius: 8px;">
                                <div style="font-size: 0.75rem; color: #6b7280;">Max</div>
                                <div style="font-weight: 600; color: #ef4444;">{{ $stats['heart_rate_stats']['resting_heart_rate']['max'] }}</div>
                            </div>
                        </div>
                    </div>
                @endif
                @if($stats['heart_rate_stats']['avg_heart_rate']['count'] == 0 && $stats['heart_rate_stats']['resting_heart_rate']['count'] == 0)
                    <p style="color: #6b7280; text-align: center;">No heart rate data yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Weekday Patterns -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="margin-right: 8px;"></i> Weekday Patterns</h3>
        </div>
        <div class="card-body">
            @if(count($stats['weekday_patterns']) > 0)
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;">
                    @foreach($stats['weekday_patterns'] as $day)
                        <div style="text-align: center; padding: 1rem; background: var(--card-bg); border-radius: 8px;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $day['day_short'] }}</div>
                            <div style="font-size: 1.25rem; font-weight: bold; color: #10b981;">
                                {{ $day['avg_steps'] ? number_format($day['avg_steps']) : '-' }}
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">avg steps</div>
                            @if($day['avg_resting_hr'])
                                <div style="font-size: 0.875rem; color: #f59e0b; margin-top: 0.25rem;">
                                    {{ $day['avg_resting_hr'] }} bpm
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #6b7280; text-align: center;">Not enough data to show patterns.</p>
            @endif
        </div>
    </div>

    <!-- Monthly History -->
    @if(count($stats['monthly_stats']) > 0)
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history" style="margin-right: 8px;"></i> Monthly History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 1000px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Entries</th>
                                <th>Total Steps</th>
                                <th>Avg Steps</th>
                                <th>Avg HR</th>
                                <th>Avg Resting HR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['monthly_stats'] as $month)
                                <tr>
                                    <td><strong>{{ $month['month_name'] }}</strong></td>
                                    <td>{{ $month['entries'] }}</td>
                                    <td style="color: #10b981; font-weight: 600;">
                                        {{ $month['total_steps'] ? number_format($month['total_steps']) : '-' }}
                                    </td>
                                    <td>{{ $month['avg_steps'] ? number_format($month['avg_steps']) : '-' }}</td>
                                    <td style="color: #ef4444;">{{ $month['avg_heart_rate'] ?? '-' }}</td>
                                    <td style="color: #f59e0b;">{{ $month['avg_resting_hr'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Trends Chart -->
    @if(count($stats['trends']) > 0)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right: 8px;"></i> Trends</h3>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" height="100"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('trendsChart').getContext('2d');
            const trendsData = @json($stats['trends']);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendsData.map(d => d.date_formatted),
                    datasets: [
                        {
                            label: 'Steps',
                            data: trendsData.map(d => d.steps),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Resting HR',
                            data: trendsData.map(d => d.resting_heart_rate),
                            borderColor: '#f59e0b',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.3,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Steps'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Heart Rate (bpm)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        </script>
    @endif
</div>

<style>
.health-accent {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.streak-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
}
.streak-badge.active {
    background: linear-gradient(135deg, #f97316 0%, #f59e0b 100%);
    color: white;
}
.streak-badge.inactive {
    background: var(--badge-muted-bg, #e5e7eb);
    color: var(--text-secondary, #6b7280);
}
.streak-fire {
    font-size: 1.25rem;
}

.goal-progress {
    height: 8px;
    background: var(--badge-muted-bg, #e5e7eb);
    border-radius: 4px;
    overflow: hidden;
}
.goal-progress .progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    transition: width 0.3s ease;
}
</style>
@endsection
