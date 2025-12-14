@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Game Backlog</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Backlog</li>
                </ul>
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['want_to_play'] }}</h3>
                        <p class="stat-label">Wil spelen</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-play"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['playing'] }}</h3>
                        <p class="stat-label">Nu aan het spelen</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['completed'] }}</h3>
                        <p class="stat-label">Uitgespeeld</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['dropped'] }}</h3>
                        <p class="stat-label">Gestopt</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('backlog.index') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Alle statussen</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ $statusFilter == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="platform" class="form-control" onchange="this.form.submit()">
                        <option value="">Alle platforms</option>
                        <option value="playstation" {{ $platformFilter == 'playstation' ? 'selected' : '' }}>PlayStation</option>
                        <option value="nintendo-switch" {{ $platformFilter == 'nintendo-switch' ? 'selected' : '' }}>Nintendo Switch</option>
                        <option value="steam" {{ $platformFilter == 'steam' ? 'selected' : '' }}>Steam</option>
                    </select>
                </div>
                @if($statusFilter || $platformFilter)
                    <a href="{{ route('backlog.index') }}" class="btn btn-secondary">Clear</a>
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
                                <th>Status</th>
                                <th>Actie</th>
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
                                    @php
                                        $route = match($game->platform_type) {
                                            'playstation' => route('playstation.games.show', $game),
                                            'nintendo-switch' => route('nintendo-switch.games.show', $game),
                                            'steam' => route('steam.games.show', $game),
                                            default => '#',
                                        };
                                    @endphp
                                    <a href="{{ $route }}" style="text-decoration: none; color: inherit;">
                                        <strong>{{ $game->name }}</strong>
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $platformInfo = match($game->platform_type) {
                                            'playstation' => ['label' => 'PlayStation', 'color' => '#003087', 'icon' => 'fas fa-gamepad'],
                                            'nintendo-switch' => ['label' => 'Switch', 'color' => '#e60012', 'icon' => 'fas fa-gamepad'],
                                            'steam' => ['label' => 'Steam', 'color' => '#1b2838', 'icon' => 'fab fa-steam'],
                                            default => ['label' => 'Unknown', 'color' => '#666', 'icon' => 'fas fa-question'],
                                        };
                                    @endphp
                                    <span class="badge" style="background: {{ $platformInfo['color'] }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                        <i class="{{ $platformInfo['icon'] }}" style="margin-right: 4px;"></i>
                                        {{ $platformInfo['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background: {{ $game->backlog_status->color() }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                        <i class="{{ $game->backlog_status->icon() }}" style="margin-right: 4px;"></i>
                                        {{ $game->backlog_status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('backlog.update-status', ['type' => $game->platform_type, 'id' => $game->id]) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()" style="width: auto; display: inline-block;">
                                            <option value="">Verwijderen</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->value }}" {{ $game->backlog_status == $status ? 'selected' : '' }}>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center" style="padding: 2rem; color: #666;">
                    @if($statusFilter || $platformFilter)
                        Geen games gevonden met de huidige filters.
                    @else
                        Je backlog is leeg. Voeg games toe vanuit de PlayStation, Nintendo Switch of Steam pagina's.
                    @endif
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
