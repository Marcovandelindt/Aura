@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Nintendo Switch Sessions</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('nintendo-switch.index') }}">Nintendo Switch</a></li>
                    <li>Sessions</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e60012 0%, #ff4444 100%);">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['total_sessions']) }}</h3>
                        <p class="stat-label">Total Sessions</p>
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
                        @php
                            $totalHours = floor($stats['total_minutes'] / 60);
                            $totalMins = $stats['total_minutes'] % 60;
                        @endphp
                        <h3 class="stat-value">{{ number_format($totalHours) }}h {{ $totalMins }}m</h3>
                        <p class="stat-label">Total Playtime</p>
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
                        <h3 class="stat-value">{{ number_format($stats['this_week']) }}</h3>
                        <p class="stat-label">This Week</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <!-- Longest Session -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-details">
                        @if($longestSession)
                            @php
                                $hours = floor($longestSession->duration_minutes / 60);
                                $mins = $longestSession->duration_minutes % 60;
                            @endphp
                            <h3 class="stat-value">{{ $hours }}h {{ $mins }}m</h3>
                            <p class="stat-label">Longest Session</p>
                            <small style="color: #666;">{{ $longestSession->game?->name ?? 'Unknown' }}</small>
                            <br><small style="color: #999;">{{ $longestSession->started_at->format('d M Y') }}</small>
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
                        @if($shortestSession)
                            <h3 class="stat-value">{{ $shortestSession->duration_minutes }}m</h3>
                            <p class="stat-label">Shortest Session</p>
                            <small style="color: #666;">{{ $shortestSession->game?->name ?? 'Unknown' }}</small>
                            <br><small style="color: #999;">{{ $shortestSession->started_at->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Shortest Session</p>
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
                        @if($longestStreak['days'] > 0)
                            <h3 class="stat-value">{{ $longestStreak['days'] }} days</h3>
                            <p class="stat-label">Longest Streak</p>
                            <small style="color: #666;">{{ $longestStreak['game']?->name ?? 'Unknown' }}</small>
                            <br><small style="color: #999;">{{ \Carbon\Carbon::parse($longestStreak['start_date'])->format('d M') }} - {{ \Carbon\Carbon::parse($longestStreak['end_date'])->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Longest Streak</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('nintendo-switch.sessions') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search games..."
                           class="form-control"
                           style="width: 100%;">
                </div>
                <div>
                    <select name="game" class="form-control" onchange="this.form.submit()">
                        <option value="">All Games</option>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}" {{ $gameId == $game->id ? 'selected' : '' }}>
                                {{ $game->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search || $gameId)
                    <a href="{{ route('nintendo-switch.sessions') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card">
        <div class="card-body">
            @if($sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Game</th>
                                <th>Date</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
                                <td>
                                    @if($session->game)
                                        <a href="{{ route('nintendo-switch.games.show', $session->game) }}" style="text-decoration: none; color: inherit;">
                                            <strong>{{ $session->game->name }}</strong>
                                        </a>
                                    @else
                                        <em>Unknown game</em>
                                    @endif
                                </td>
                                <td>
                                    <span title="{{ $session->started_at->diffForHumans() }}">
                                        {{ $session->started_at->format('l, d M Y') }}
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: #e60012;">{{ $session->formatted_duration }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $sessions->links() }}
            @else
                <p class="text-center" style="padding: 2rem;">
                    No sessions found.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
