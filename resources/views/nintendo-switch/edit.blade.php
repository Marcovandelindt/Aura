@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Edit Game</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('nintendo-switch.index') }}">Nintendo Switch</a></li>
                    <li><a href="{{ route('nintendo-switch.games.show', $game) }}">{{ $game->name }}</a></li>
                    <li>Edit</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('nintendo-switch.games.show', $game) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('nintendo-switch.games.update', $game) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="name" class="form-label">Game Name *</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $game->name) }}" required>
                    @error('name')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>

                @if($game->image_url)
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label">Current Image</label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="remove_image" value="1">
                                <span style="color: #dc3545;">Remove image</span>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="image" class="form-label">{{ $game->image_url ? 'Replace Image' : 'Cover Image' }} (optional)</label>
                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp">
                    @error('image')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                    <small class="form-text" style="color: #666;">Max 2MB. Formats: JPG, PNG, WebP.</small>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save" style="margin-right: 6px;"></i> Save Changes
                    </button>
                    <a href="{{ route('nintendo-switch.games.show', $game) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
