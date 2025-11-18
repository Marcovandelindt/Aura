@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>PlayStation Games</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>PlayStation</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('playstation.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar" style="margin-right: 8px;"></i>
                    Statistics
                </a>
                <form action="{{ route('playstation.sync') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>
                        Sync Now
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #003087 0%, #0070cc 100%);">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['total_games']) }}</h3>
                        <p class="stat-label">Total Games</p>
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
                        <h3 class="stat-value">{{ number_format($stats['total_hours'], 1) }}h</h3>
                        <p class="stat-label">Total Playtime</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['total_sessions']) }}</h3>
                        <p class="stat-label">Total Sessions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sessions -->
    @if($recentSessions->count() > 0)
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="font-size: 1.1rem; margin: 0;">
                    <i class="fas fa-history" style="margin-right: 8px; color: #667eea;"></i>
                    Recent Sessions
                </h3>
                <a href="{{ route('playstation.sessions') }}" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Platform</th>
                            <th>Duration</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSessions as $session)
                        <tr>
                            <td>
                                @if($session->game)
                                    <strong>{{ $session->game->name }}</strong>
                                @else
                                    <em>Unknown game</em>
                                @endif
                            </td>
                            <td>
                                @if($session->game)
                                    <span class="badge" style="background: {{ match($session->game->platform) {
                                        'PS5' => '#003087',
                                        'PS4' => '#00439c',
                                        'PS3' => '#006cb7',
                                        'PSVITA' => '#00acee',
                                        default => '#666'
                                    } }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                        {{ $session->game->platform }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $session->formatted_duration }}</td>
                            <td>
                                <span title="{{ $session->started_at->diffForHumans() }}">
                                    {{ $session->started_at->format('d M H:i') }}
                                </span>
                            </td>
                            <td>
                                @if($session->ended_at)
                                    <span title="{{ $session->ended_at->diffForHumans() }}">
                                        {{ $session->ended_at->format('H:i') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('playstation.index') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search games..."
                           class="form-control"
                           style="width: 100%;">
                </div>
                <div>
                    <select name="platform" class="form-control" onchange="this.form.submit()">
                        <option value="">All Platforms</option>
                        @foreach($stats['platforms'] as $platformName => $count)
                            <option value="{{ $platformName }}" {{ $platform == $platformName ? 'selected' : '' }}>
                                {{ $platformName }} ({{ $count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="hours" {{ $sort == 'hours' ? 'selected' : '' }}>Most Played</option>
                        <option value="sessions" {{ $sort == 'sessions' ? 'selected' : '' }}>Most Sessions</option>
                        <option value="last_played" {{ $sort == 'last_played' ? 'selected' : '' }}>Recently Played</option>
                        <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search || $platform || $sort != 'hours')
                    <a href="{{ route('playstation.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Games List -->
    <div class="card">
        <div class="card-body">
            @if($games->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50"></th>
                                <th>Game</th>
                                <th>Platform</th>
                                <th>Playtime</th>
                                <th>Sessions</th>
                                <th>Avg Session</th>
                                <th>Last Played</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($games as $game)
                            <tr>
                                <td>
                                    @if($game->image_url)
                                        <img src="{{ $game->image_url }}"
                                             alt="{{ $game->name }}"
                                             style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 4px; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-gamepad" style="color: #999;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('playstation.games.show', $game) }}" style="text-decoration: none; color: inherit;">
                                        <strong>{{ $game->name }}</strong>
                                    </a>
                                    @if($game->completion_percentage)
                                        <br>
                                        <small style="color: #666;">
                                            <i class="fas fa-trophy" style="color: #ffd700;"></i>
                                            {{ $game->completion_percentage }}%
                                            @if($game->trophies)
                                                ({{ $game->trophies }} trophies)
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background: {{ match($game->platform) {
                                        'PS5' => '#003087',
                                        'PS4' => '#00439c',
                                        'PS3' => '#006cb7',
                                        'PSVITA' => '#00acee',
                                        default => '#666'
                                    } }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                        {{ $game->platform }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $game->formatted_hours }}</strong>
                                </td>
                                <td>{{ number_format($game->sessions) }}</td>
                                <td>{{ $game->formatted_avg_session }}</td>
                                <td>
                                    @if($game->last_played_at)
                                        <span title="{{ $game->last_played_at->format('Y-m-d') }}">
                                            {{ $game->last_played_at->diffForHumans() }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $games->links() }}
            @else
                <p class="text-center" style="padding: 2rem;">
                    No games found. Try syncing your PlayStation data.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
