@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Edit Game</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('steam.index') }}">Steam</a></li>
                    <li><a href="{{ route('steam.games.show', $game) }}">{{ $game->name }}</a></li>
                    <li>Edit</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('steam.games.show', $game) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Game Info (Read-only) -->
            <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #eee;">
                @if($game->image_url)
                    <img src="{{ $game->image_url }}" alt="{{ $game->name }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                @else
                    <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-steam" style="font-size: 1.5rem; color: #9ca3af;"></i>
                    </div>
                @endif
                <div>
                    <h2 style="margin: 0 0 0.5rem 0;">{{ $game->name }}</h2>
                    <span style="color: #666; font-size: 0.875rem;">
                        {{ $game->formatted_playtime }} played
                    </span>
                </div>
            </div>

            <form action="{{ route('steam.games.update', $game) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="price" class="form-label">Price (optional)</label>
                    <div style="position: relative; max-width: 200px;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #666;"></span>
                        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $game->price) }}" step="0.01" min="0" placeholder="0.00" style="padding-left: 28px;" autofocus>
                    </div>
                    @error('price')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                    <small class="form-text" style="color: #666;">What did you pay for this game?</small>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save" style="margin-right: 6px;"></i> Save Changes
                    </button>
                    <a href="{{ route('steam.games.show', $game) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
