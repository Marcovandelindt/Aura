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
                <button type="button" class="btn btn-secondary" onclick="openWatDeedIk()">
                    <i class="fas fa-calendar-day" style="margin-right: 8px;"></i>
                    Wat deed ik?
                </button>
                <button type="button" class="btn btn-primary" onclick="openRecommendation()" style="background: linear-gradient(135deg, #003087 0%, #0070cc 100%); border: none;">
                    <i class="fas fa-magic" style="margin-right: 8px;"></i>
                    Aanbeveling
                </button>
                <a href="{{ route('playstation.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar" style="margin-right: 8px;"></i>
                    Statistics
                </a>
                <a href="{{ route('playstation.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i>
                    Add Game
                </a>
                <form action="{{ route('playstation.sync') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>
                        Sync
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
                    <select name="playtime" class="form-control" onchange="this.form.submit()">
                        <option value="">All Playtime</option>
                        <option value="zero" {{ $playtime == 'zero' ? 'selected' : '' }}>No Playtime</option>
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
                @if($search || $platform || $playtime || $sort != 'hours')
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
                                <td>{{ number_format($game->calculated_sessions) }}</td>
                                <td>{{ $game->formatted_avg_session }}</td>
                                <td>
                                    @php
                                        $lastPlayed = $game->sessions_max_started_at
                                            ? \Carbon\Carbon::parse($game->sessions_max_started_at)
                                            : $game->last_played_at;
                                    @endphp
                                    @if($lastPlayed)
                                        <span title="{{ $lastPlayed->format('Y-m-d') }}">
                                            {{ $lastPlayed->diffForHumans() }}
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

<x-game-recommendation-modal
    platform="playstation"
    platform-name="PlayStation"
    accent-color="#003087"
/>

<!-- Wat deed ik? Modal -->
<div id="watDeedIkModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card, #1a1a2e); border-radius:12px; padding:2rem; width:100%; max-width:540px; max-height:80vh; overflow-y:auto; position:relative; color:#fff;">
        <button onclick="closeWatDeedIk()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:inherit; font-size:1.25rem; cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>

        <h2 style="margin:0 0 1.5rem; font-size:1.25rem; color:#fff;">Wat deed ik?</h2>

        <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem;">
            <input type="date" id="watDeedIkDate" value="{{ now()->toDateString() }}"
                style="flex:1; padding:0.5rem 0.75rem; border-radius:8px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.05); color:#fff; font-size:0.95rem; color-scheme:dark;">
            <button onclick="fetchWatDeedIk()" class="btn btn-primary" style="background:linear-gradient(135deg, #003087 0%, #0070cc 100%); border:none; white-space:nowrap;">
                <i class="fas fa-search" style="margin-right:6px;"></i>Zoeken
            </button>
        </div>

        <div id="watDeedIkResult"></div>
    </div>
</div>

<script>
function openWatDeedIk() {
    document.getElementById('watDeedIkModal').style.display = 'flex';
    fetchWatDeedIk();
}

function closeWatDeedIk() {
    document.getElementById('watDeedIkModal').style.display = 'none';
}

document.getElementById('watDeedIkDate').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') fetchWatDeedIk();
});

document.getElementById('watDeedIkModal').addEventListener('click', function(e) {
    if (e.target === this) closeWatDeedIk();
});

async function fetchWatDeedIk() {
    const date = document.getElementById('watDeedIkDate').value;
    const result = document.getElementById('watDeedIkResult');

    if (!date) return;

    result.innerHTML = '<p style="color:#888; text-align:center;"><i class="fas fa-spinner fa-spin"></i> Laden...</p>';

    try {
        const res = await fetch(`{{ route('playstation.wat-deed-ik') }}?date=${date}`);
        const data = await res.json();

        if (!data.sessions.length) {
            result.innerHTML = '<p style="color:#888; text-align:center;">Geen PlayStation sessies gevonden op deze datum.</p>';
            return;
        }

        const totalHours = Math.floor(data.total_minutes / 60);
        const totalMins = data.total_minutes % 60;
        const totalStr = totalHours > 0 ? `${totalHours}h ${totalMins}m` : `${totalMins}m`;

        let html = `<p style="color:#888; margin:0 0 1rem; font-size:0.875rem;">${data.date} &mdash; ${totalStr} totaal</p>`;

        data.sessions.forEach(game => {
            html += `
                <a href="${game.url}" style="display:flex; gap:1rem; align-items:flex-start; padding:0.875rem 0; border-top:1px solid rgba(255,255,255,0.08); text-decoration:none; color:inherit; transition:opacity 0.15s;" onmouseover="this.style.opacity='0.75'" onmouseout="this.style.opacity='1'">
                    ${game.image_url
                        ? `<img src="${game.image_url}" style="width:52px; height:52px; object-fit:cover; border-radius:8px; flex-shrink:0;">`
                        : `<div style="width:52px; height:52px; border-radius:8px; background:rgba(255,255,255,0.05); flex-shrink:0; display:flex; align-items:center; justify-content:center;"><i class="fas fa-gamepad" style="color:#555;"></i></div>`
                    }
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; justify-content:space-between; align-items:baseline; gap:0.5rem;">
                            <span style="font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${game.name}</span>
                            <span style="color:#0070cc; font-weight:600; white-space:nowrap;">${game.duration}</span>
                        </div>
                        <div style="margin-top:0.25rem;">
                            ${game.sessions.map(s => `
                                <span style="font-size:0.8rem; color:#888;">${s.started_at}${s.ended_at ? ' – ' + s.ended_at : ''}</span>
                            `).join('<span style="color:#444; margin:0 0.25rem;">·</span>')}
                        </div>
                    </div>
                </a>`;
        });

        result.innerHTML = html;
    } catch (e) {
        result.innerHTML = '<p style="color:#e74c3c;">Er ging iets mis. Probeer opnieuw.</p>';
    }
}
</script>
@endsection
