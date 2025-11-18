@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>PlayStation Sessions</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('playstation.index') }}">PlayStation</a></li>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #003087 0%, #0070cc 100%);">
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

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('playstation.sessions') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
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
                                {{ $game->name }} ({{ $game->platform }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search || $gameId)
                    <a href="{{ route('playstation.sessions') }}" class="btn btn-secondary">Clear</a>
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
                                <th>Platform</th>
                                <th>Duration</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
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
                                        {{ $session->started_at->format('d M Y H:i') }}
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
