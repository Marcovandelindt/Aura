@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $game->name }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('steam.index') }}">Steam</a></li>
                    <li>{{ $game->name }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <form action="{{ route('backlog.update-status', ['type' => 'steam', 'id' => $game->id]) }}" method="POST" style="display: inline;">
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
                <a href="{{ $game->steam_url }}" target="_blank" class="btn btn-secondary">
                    <i class="fab fa-steam" style="margin-right: 8px;"></i>
                    View on Steam
                </a>
                <a href="{{ route('steam.games.edit', $game) }}" class="btn btn-secondary">
                    <i class="fas fa-edit" style="margin-right: 8px;"></i>
                    Edit
                </a>
                <a href="{{ route('steam.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Game Info -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div style="display: flex; gap: 2rem; align-items: flex-start;">
                @if($game->image_url)
                    <img src="{{ $game->image_url }}" alt="{{ $game->name }}"
                         style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover;">
                @else
                    <div style="width: 120px; height: 120px; border-radius: 8px; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-steam" style="color: #999; font-size: 3rem;"></i>
                    </div>
                @endif

                <div style="flex: 1;">
                    <h2 style="margin: 0 0 1rem 0;">{{ $game->name }}</h2>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
                        <div>
                            <div style="color: #666; font-size: 0.875rem; margin-bottom: 4px;">Total Playtime</div>
                            <div style="font-size: 1.5rem; font-weight: 600;">{{ $game->formatted_playtime }}</div>
                        </div>

                        <div>
                            <div style="color: #666; font-size: 0.875rem; margin-bottom: 4px;">Hours</div>
                            <div style="font-size: 1.5rem; font-weight: 600;">{{ number_format($game->playtime_hours, 1) }}h</div>
                        </div>

                        @if($game->playtime_2weeks_minutes)
                        <div>
                            <div style="color: #666; font-size: 0.875rem; margin-bottom: 4px;">Last 2 Weeks</div>
                            <div style="font-size: 1.5rem; font-weight: 600;">{{ $game->playtime_2weeks_hours }}h</div>
                        </div>
                        @endif

                        <div>
                            <div style="color: #666; font-size: 0.875rem; margin-bottom: 4px;">Last Played</div>
                            <div style="font-size: 1.25rem; font-weight: 500;">
                                @if($game->last_played_at)
                                    {{ $game->last_played_at->format('d M Y') }}
                                    <div style="font-size: 0.875rem; color: #666;">{{ $game->last_played_at->diffForHumans() }}</div>
                                @else
                                    Never
                                @endif
                            </div>
                        </div>

                        @if($game->price)
                        <div>
                            <div style="color: #666; font-size: 0.875rem; margin-bottom: 4px;">Price Paid</div>
                            <div style="font-size: 1.5rem; font-weight: 600; color: #10b981;">
                                {{ number_format($game->price, 2, ',', '.') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $game->playtime_minutes }}</h3>
                        <p class="stat-label">Total Minutes</p>
                    </div>
                </div>
            </div>
        </div>

        @if($game->playtime_minutes > 0 && $game->price)
        @php
            $costPerHour = $game->playtime_hours > 0 ? $game->price / $game->playtime_hours : 0;
            $costPerMinute = $game->playtime_minutes > 0 ? $game->price / $game->playtime_minutes : 0;

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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($costPerMinute, 4, ',', '.') }}/m</h3>
                        <p class="stat-label">Cost per Minute</p>
                    </div>
                </div>
            </div>
        </div>

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

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);">
                        <i class="fab fa-steam"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $game->steam_appid }}</h3>
                        <p class="stat-label">Steam App ID</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
