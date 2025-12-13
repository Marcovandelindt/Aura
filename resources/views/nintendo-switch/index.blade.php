@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Nintendo Switch Games</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Nintendo Switch</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('nintendo-switch.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar" style="margin-right: 8px;"></i>
                    Statistics
                </a>
                <a href="{{ route('nintendo-switch.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i>
                    Add Game
                </a>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e60012 0%, #ff4444 100%);">
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

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">€{{ number_format($stats['total_spent'] ?? 0, 2, ',', '.') }}</h3>
                        <p class="stat-label">Total Spent</p>
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
                    <i class="fas fa-history" style="margin-right: 8px; color: #e60012;"></i>
                    Recent Sessions
                </h3>
                <a href="{{ route('nintendo-switch.sessions') }}" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Date</th>
                            <th>Duration</th>
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
                                <span title="{{ $session->started_at->diffForHumans() }}">
                                    {{ $session->started_at->format('d M Y') }}
                                </span>
                            </td>
                            <td style="font-weight: 600; color: #e60012;">{{ $session->formatted_duration }}</td>
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
            <form method="GET" action="{{ route('nintendo-switch.index') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search games..."
                           class="form-control"
                           style="width: 100%;">
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
                @if($search || $sort != 'hours')
                    <a href="{{ route('nintendo-switch.index') }}" class="btn btn-secondary">Clear</a>
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
                                <th>Playtime</th>
                                <th>Sessions</th>
                                <th>Avg Session</th>
                                <th>Last Played</th>
                                <th width="100">Actions</th>
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
                                        <div style="width: 40px; height: 40px; border-radius: 4px; background: #e60012; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-gamepad" style="color: white;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('nintendo-switch.games.show', $game) }}" style="text-decoration: none; color: inherit;">
                                        <strong>{{ $game->name }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $game->formatted_hours }}</strong>
                                </td>
                                <td>{{ number_format($game->calculated_sessions) }}</td>
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
                                <td>
                                    <a href="{{ route('nintendo-switch.games.edit', $game) }}" class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $games->links() }}
            @else
                <p class="text-center" style="padding: 2rem;">
                    No games found. <a href="{{ route('nintendo-switch.create') }}">Add your first game</a>.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
