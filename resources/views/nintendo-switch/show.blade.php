@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $game->name }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('nintendo-switch.index') }}">Nintendo Switch</a></li>
                    <li>{{ $game->name }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <form action="{{ route('backlog.update-status', ['type' => 'nintendo-switch', 'id' => $game->id]) }}" method="POST" style="display: inline;">
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
                <a href="{{ route('nintendo-switch.games.edit', $game) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('nintendo-switch.games.destroy', $game) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? This will delete all sessions too.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                <a href="{{ route('nintendo-switch.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Game Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                @if($game->image_url)
                    <img src="{{ $game->image_url }}" alt="{{ $game->name }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                @else
                    <div style="width: 120px; height: 120px; background: #e60012; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-gamepad" style="font-size: 2rem; color: white;"></i>
                    </div>
                @endif
                <div>
                    <h2 style="margin: 0 0 0.5rem 0;">{{ $game->name }}</h2>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge" style="background: #e60012; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                            Nintendo Switch
                        </span>
                        @if($game->price)
                            <span class="badge" style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                €{{ number_format($game->price, 2, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e60012 0%, #ff4444 100%);">
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

    <!-- Add Session Form -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-plus" style="margin-right: 8px;"></i> Add Session</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('nintendo-switch.sessions.store', $game) }}" method="POST" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                @csrf
                <div style="flex: 1; min-width: 200px;">
                    <label for="played_at" class="form-label">Date</label>
                    <input type="date" name="played_at" id="played_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required autofocus>
                    @error('played_at')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div style="min-width: 150px;">
                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" min="1" placeholder="60" required>
                    @error('duration_minutes')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus" style="margin-right: 6px;"></i> Add Session
                </button>
            </form>
        </div>
    </div>

    <!-- Monthly Activity -->
    @if($monthlyStats->count() > 0)
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="margin-right: 8px;"></i> Monthly Activity</h3>
        </div>
        <div class="card-body">
            <div class="top-list">
                @foreach($monthlyStats as $month)
                    <div class="top-list-item" style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                        <div class="item-info">
                            <div class="item-name">{{ \Carbon\Carbon::createFromDate($month->year, $month->month, 1)->format('F Y') }}</div>
                            <div class="item-details" style="font-size: 0.75rem; color: #666;">
                                {{ $month->sessions }} sessions
                            </div>
                        </div>
                        <div style="font-weight: 600; color: #e60012;">{{ number_format($month->minutes / 60, 1) }}h</div>
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
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Duration</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
                                <td>{{ $session->started_at->format('l, d M Y') }}</td>
                                <td style="font-weight: 600; color: #e60012;">
                                    @if($session->duration_minutes >= 60)
                                        {{ floor($session->duration_minutes / 60) }}h {{ $session->duration_minutes % 60 }}m
                                    @else
                                        {{ $session->duration_minutes }}m
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('nintendo-switch.sessions.destroy', $session) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this session?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1.5rem;">
                    {{ $sessions->links() }}
                </div>
            @else
                <p class="no-data">No sessions found for this game. Add your first session above!</p>
            @endif
        </div>
    </div>
</div>
@endsection
