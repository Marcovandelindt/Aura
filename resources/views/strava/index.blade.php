@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Strava</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Strava</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('strava.heatmap') }}" class="btn btn-secondary">
                    <i class="fas fa-map"></i>
                    Heatmap
                </a>
                <a href="{{ route('strava.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i>
                    Statistics
                </a>
                @if(!$isConnected)
                    <a href="{{ route('strava.auth') }}" class="btn btn-primary">
                        <i class="fas fa-link"></i>
                        Connect Strava
                    </a>
                @else
                    <a href="{{ route('strava.auth') }}" class="btn btn-secondary">
                        <i class="fas fa-sync"></i>
                        Reconnect
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(!$isConnected)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Strava is not connected. <a href="{{ route('strava.auth') }}">Connect your account</a> and run <code>php artisan strava:sync</code> to import your activities.
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_activities']) }}</div>
            <div class="stat-label">Total Activities</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_distance_km'], 1) }} km</div>
            <div class="stat-label">Total Distance</div>
        </div>
        <div class="stat-card">
            @php
                $hours = intdiv($stats['total_moving_time'], 3600);
                $minutes = intdiv($stats['total_moving_time'] % 3600, 60);
            @endphp
            <div class="stat-value">{{ $hours }}h {{ $minutes }}m</div>
            <div class="stat-label">Total Moving Time</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_elevation']) }} m</div>
            <div class="stat-label">Total Elevation</div>
        </div>
    </div>

    <!-- Filter -->
    @if($types->isNotEmpty())
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('strava.index') }}" style="display: flex; gap: 1rem; align-items: flex-end;">
                <div>
                    <label style="display: block; font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Type</label>
                    <select name="type" class="form-control">
                        <option value="">All types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                @if(request('type'))
                    <a href="{{ route('strava.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>
    @endif

    <!-- Activities Table -->
    <div class="card">
        <div class="card-header">
            <h3>Activities</h3>
        </div>
        <div class="card-body">
            @if($activities->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 2rem 0;">
                    No activities yet. Run <code>php artisan strava:sync</code> to import your activities.
                </p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Distance</th>
                            <th>Duration</th>
                            <th>Elevation</th>
                            <th>Avg Speed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr style="cursor: pointer;" onclick="window.location='{{ route('strava.show', $activity) }}'">
                                <td>{{ $activity->start_date_local->format('d M Y') }}</td>
                                <td>{{ $activity->name }}</td>
                                <td>{{ $activity->sport_type }}</td>
                                <td>{{ $activity->distance_in_km }} km</td>
                                <td>{{ $activity->moving_time_pretty }}</td>
                                <td>{{ round($activity->total_elevation_gain) }} m</td>
                                <td>
                                    @if($activity->average_speed)
                                        {{ round($activity->average_speed * 3.6, 1) }} km/h
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 1rem;">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
