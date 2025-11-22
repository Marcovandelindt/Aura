@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Game Music</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Games</li>
                </ul>
            </div>
        </div>
    </div>

    @if($games->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-gamepad" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">No games yet</h3>
                <p style="color: #6b7280;">Add the "Game" mood to your tracks to start tracking game music.</p>
            </div>
        </div>
    @else
        <div class="games-grid">
            @foreach($games as $game)
                <a href="{{ route('games.show', $game->slug) }}" class="game-card">
                    @if($game->cover_image)
                        <div class="game-cover">
                            <img src="{{ $game->cover_image }}" alt="{{ $game->name }}">
                        </div>
                    @else
                        <div class="game-cover game-cover-placeholder">
                            <i class="fas fa-gamepad"></i>
                        </div>
                    @endif
                    <div class="game-info">
                        <h3 class="game-title">{{ $game->name }}</h3>
                        @if($game->developer)
                            <p class="game-developer">{{ $game->developer }}</p>
                        @endif
                        <div class="game-stats">
                            <span class="game-stat">
                                <i class="fas fa-music"></i>
                                {{ $game->track_count }} {{ Str::plural('track', $game->track_count) }}
                            </span>
                            @if($game->release_year)
                                <span class="game-stat">
                                    <i class="fas fa-calendar"></i>
                                    {{ $game->release_year }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
