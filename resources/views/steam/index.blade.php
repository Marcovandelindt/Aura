@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Steam Games</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Steam</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="openRecommendation()" style="background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: none;">
                    <i class="fas fa-magic" style="margin-right: 8px;"></i>
                    Aanbeveling
                </button>
                <form action="{{ route('steam.sync') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>
                        Sync from Steam
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);">
                        <i class="fab fa-steam"></i>
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
                        <h3 class="stat-value">{{ number_format($stats['played_games']) }}</h3>
                        <p class="stat-label">Played</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['never_played']) }}</h3>
                        <p class="stat-label">Never Played</p>
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
                        <h3 class="stat-value">{{ number_format($stats['total_spent'] ?? 0, 2, ',', '.') }}</h3>
                        <p class="stat-label">Total Spent</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Most Played -->
        @if($mostPlayed->count() > 0)
        <div class="card">
            <div class="card-body">
                <h3 style="font-size: 1.1rem; margin: 0 0 1rem 0;">
                    <i class="fas fa-trophy" style="margin-right: 8px; color: #ffd700;"></i>
                    Most Played
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($mostPlayed as $game)
                    <a href="{{ route('steam.games.show', $game) }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit;">
                        @if($game->image_url)
                            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" style="width: 32px; height: 32px; border-radius: 4px; object-fit: cover;">
                        @else
                            <div style="width: 32px; height: 32px; border-radius: 4px; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-steam" style="color: #999; font-size: 0.8rem;"></i>
                            </div>
                        @endif
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $game->name }}</div>
                        </div>
                        <div style="font-size: 0.875rem; color: #666; white-space: nowrap;">{{ $game->formatted_playtime }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Recently Played -->
        @if($recentlyPlayed->count() > 0)
        <div class="card">
            <div class="card-body">
                <h3 style="font-size: 1.1rem; margin: 0 0 1rem 0;">
                    <i class="fas fa-history" style="margin-right: 8px; color: #667eea;"></i>
                    Recently Played
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($recentlyPlayed as $game)
                    <a href="{{ route('steam.games.show', $game) }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit;">
                        @if($game->image_url)
                            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" style="width: 32px; height: 32px; border-radius: 4px; object-fit: cover;">
                        @else
                            <div style="width: 32px; height: 32px; border-radius: 4px; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-steam" style="color: #999; font-size: 0.8rem;"></i>
                            </div>
                        @endif
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $game->name }}</div>
                        </div>
                        <div style="font-size: 0.875rem; color: #666; white-space: nowrap;">
                            {{ $game->last_played_at?->diffForHumans() }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('steam.index') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search games..."
                           class="form-control"
                           style="width: 100%;">
                </div>
                <div>
                    <select name="filter" class="form-control" onchange="this.form.submit()">
                        <option value="">All Games</option>
                        <option value="played" {{ $filter == 'played' ? 'selected' : '' }}>Played</option>
                        <option value="never_played" {{ $filter == 'never_played' ? 'selected' : '' }}>Never Played</option>
                        <option value="recent" {{ $filter == 'recent' ? 'selected' : '' }}>Recently Played (30 days)</option>
                    </select>
                </div>
                <div>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="playtime" {{ $sort == 'playtime' ? 'selected' : '' }}>Most Played</option>
                        <option value="recent_playtime" {{ $sort == 'recent_playtime' ? 'selected' : '' }}>Recent Playtime</option>
                        <option value="last_played" {{ $sort == 'last_played' ? 'selected' : '' }}>Last Played</option>
                        <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search || $filter || $sort != 'playtime')
                    <a href="{{ route('steam.index') }}" class="btn btn-secondary">Clear</a>
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
                                <th>Recent (2 weeks)</th>
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
                                            <i class="fab fa-steam" style="color: #999;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('steam.games.show', $game) }}" style="text-decoration: none; color: inherit;">
                                        <strong>{{ $game->name }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $game->formatted_playtime }}</strong>
                                </td>
                                <td>
                                    @if($game->playtime_2weeks_minutes)
                                        {{ round($game->playtime_2weeks_minutes / 60, 1) }}h
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($game->last_played_at)
                                        <span title="{{ $game->last_played_at->format('Y-m-d H:i') }}">
                                            {{ $game->last_played_at->diffForHumans() }}
                                        </span>
                                    @else
                                        Never
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
                    No games found. Configure your Steam API key and sync your library.
                </p>
            @endif
        </div>
    </div>
</div>

<x-game-recommendation-modal
    platform="steam"
    platform-name="Steam"
    accent-color="#1b2838"
/>
@endsection
