@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $game->name }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('playstation.index') }}">PlayStation</a></li>
                    <li>{{ $game->name }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <form action="{{ route('backlog.update-status', ['type' => 'playstation', 'id' => $game->id]) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="form-control" onchange="this.form.submit()" style="width: auto;">
                        <option value="" {{ !$game->backlog_status ? 'selected' : '' }}>+ Backlog</option>
                        @foreach(\App\Enums\BacklogStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ $game->backlog_status == $status ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @if($game->sessions()->count() > 0 && !$game->exclude_from_sync)
                    <form action="{{ route('playstation.games.convert-to-manual', $game) }}" method="POST" style="display: inline;" onsubmit="return confirm('This will delete all {{ $game->sessions()->count() }} sessions and switch to manual time tracking. Are you sure?');">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-hand-paper"></i> Manual
                        </button>
                    </form>
                @endif
                <a href="{{ route('playstation.games.edit', $game) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('playstation.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Game Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                @if($game->image_url)
                    <img src="{{ $game->image_url }}" alt="{{ $game->name }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                @else
                    <div style="width: 120px; height: 120px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-gamepad" style="font-size: 2rem; color: #9ca3af;"></i>
                    </div>
                @endif
                <div>
                    <h2 style="margin: 0 0 0.5rem 0;">{{ $game->name }}</h2>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <span class="badge" style="background: {{ match($game->platform) {
                            'PS5' => '#003087',
                            'PS4' => '#00439c',
                            'PS3' => '#006cb7',
                            'PSVITA' => '#00acee',
                            default => '#666'
                        } }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                            {{ $game->platform }}
                        </span>
                        @if($game->price)
                            <span class="badge" style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                €{{ number_format($game->price, 2, ',', '.') }}
                            </span>
                        @endif
                        @if($game->exclude_from_sync)
                            <span class="badge" style="background: #f59e0b; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                Manual tracking
                            </span>
                        @endif
                        @if($game->completion_percentage)
                            <span style="color: #666; font-size: 0.875rem;">
                                <i class="fas fa-trophy"></i> {{ $game->completion_percentage }}% complete
                            </span>
                        @endif
                        @if($game->user_rating)
                            <span class="badge" style="background: #8b5cf6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                <i class="fas fa-star" style="margin-right: 4px;"></i>{{ $game->user_rating }}/10
                            </span>
                        @endif
                        @if($game->critic_rating)
                            <span class="badge" style="background: {{ $game->critic_rating >= 75 ? '#10b981' : ($game->critic_rating >= 50 ? '#f59e0b' : '#ef4444') }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                <i class="fas fa-newspaper" style="margin-right: 4px;"></i>{{ $game->critic_rating }}
                            </span>
                        @endif
                        @if($game->play_mode)
                            <span class="badge" style="background: {{ $game->play_mode->color() }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                <i class="{{ $game->play_mode->icon() }}" style="margin-right: 4px;"></i>{{ $game->play_mode->label() }}
                            </span>
                        @endif
                        @if($game->main_story_completed)
                            <span class="badge" style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                <i class="fas fa-flag-checkered" style="margin-right: 4px;"></i>Story Completed
                            </span>
                        @endif
                    </div>
                    @if($game->genres->count() > 0)
                    <div style="display: flex; gap: 0.375rem; flex-wrap: wrap; margin-top: 0.5rem;">
                        @foreach($game->genres as $genre)
                            <span style="background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                                {{ $genre->name }}
                            </span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #003087 0%, #0070cc 100%);">
                        <i class="fas fa-gamepad"></i>
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
                        <h3 class="stat-value">{{ number_format($stats['total_hours'], 1) }}h</h3>
                        <p class="stat-label">Total Playtime</p>
                        @if($stats['manual_minutes'] > 0)
                            <small style="color: #999;">
                                incl. {{ number_format($stats['manual_minutes'] / 60, 1) }}h manual
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        @php
                            $avgHours = floor($stats['avg_session_minutes'] / 60);
                            $avgMins = $stats['avg_session_minutes'] % 60;
                        @endphp
                        <h3 class="stat-value">
                            @if($avgHours > 0)
                                {{ $avgHours }}h {{ $avgMins }}m
                            @else
                                {{ $avgMins }}m
                            @endif
                        </h3>
                        <p class="stat-label">Avg Session</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">
                            @if($stats['first_played'])
                                {{ \Carbon\Carbon::parse($stats['first_played'])->diffInDays($stats['last_played']) + 1 }}
                            @else
                                0
                            @endif
                        </h3>
                        <p class="stat-label">Days Span</p>
                    </div>
                </div>
            </div>
        </div>

        @if($stats['total_hours'] > 0 && $game->price)
        @php
            $costPerHour = $game->price / $stats['total_hours'];

            if ($costPerHour < 0.50) {
                $valueLabel = 'Excellent';
                $valueColor = '#10b981';
                $valueIcon = 'fas fa-star';
            } elseif ($costPerHour < 2) {
                $valueLabel = 'Good';
                $valueColor = '#3b82f6';
                $valueIcon = 'fas fa-thumbs-up';
            } elseif ($costPerHour < 6) {
                $valueLabel = 'Fair';
                $valueColor = '#f59e0b';
                $valueIcon = 'fas fa-meh';
            } else {
                $valueLabel = 'Poor';
                $valueColor = '#ef4444';
                $valueIcon = 'fas fa-thumbs-down';
            }
        @endphp
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: {{ $valueColor }};">
                        <i class="{{ $valueIcon }}"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value" style="color: {{ $valueColor }};">{{ $valueLabel }}</h3>
                        <p class="stat-label">Value ({{ number_format($costPerHour, 2, ',', '.') }}/h)</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Session Records -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Longest Session -->
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="stat-details">
                        @if($longestSession)
                            @php
                                $hours = floor($longestSession->duration_minutes / 60);
                                $mins = $longestSession->duration_minutes % 60;
                            @endphp
                            <h3 class="stat-value">{{ $hours }}h {{ $mins }}m</h3>
                            <p class="stat-label">Longest Session</p>
                            <small style="color: #999;">{{ $longestSession->started_at->format('d M Y') }}</small>
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
                            <small style="color: #999;">{{ $shortestSession->started_at->format('d M Y') }}</small>
                        @else
                            <h3 class="stat-value">-</h3>
                            <p class="stat-label">Shortest Session</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Activity -->
    @if($monthlyStats->count() > 0)
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="margin-right: 8px;"></i> Monthly Activity</h3>
        </div>
        <div class="card-body">
            <div class="top-list" style="max-height: 1000px; overflow-y: auto;">
                @foreach($monthlyStats as $month)
                    <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                        <div class="item-info">
                            <div class="item-name">{{ \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('F Y') }}</div>
                            <div class="item-details" style="font-size: 0.75rem; color: #666;">
                                {{ $month->sessions }} sessions
                            </div>
                        </div>
                        <div style="font-weight: 600; color: #667eea;">{{ number_format($month->minutes / 60, 1) }}h</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Sessions List -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history" style="margin-right: 8px;"></i> All Sessions</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">{{ $sessions->total() }} sessions total</p>
        </div>
        <div class="card-body">
            @if($sessions->count() > 0)
                <div class="top-list">
                    @foreach($sessions as $session)
                        <div class="top-list-item" style="padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                            <div class="item-info">
                                <div class="item-name">{{ $session->started_at->format('l, d M Y') }}</div>
                                <div class="item-details" style="font-size: 0.875rem; color: #666;">
                                    {{ $session->started_at->format('H:i') }} - {{ $session->ended_at->format('H:i') }}
                                </div>
                            </div>
                            <div style="font-weight: 600; color: #667eea;">
                                @if($session->duration_minutes >= 60)
                                    {{ floor($session->duration_minutes / 60) }}h {{ $session->duration_minutes % 60 }}m
                                @else
                                    {{ $session->duration_minutes }}m
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 1.5rem;">
                    {{ $sessions->links() }}
                </div>
            @else
                <p class="no-data">No sessions found for this game.</p>
            @endif
        </div>
    </div>
</div>
@endsection
