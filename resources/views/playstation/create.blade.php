@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Add PlayStation Game</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('playstation.index') }}">PlayStation</a></li>
                    <li>Add Game</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('playstation.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('playstation.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="name" class="form-label">Game Name *</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="platform" class="form-label">Platform *</label>
                    <select name="platform" id="platform" class="form-control @error('platform') is-invalid @enderror" required>
                        <option value="">Select platform...</option>
                        <option value="PS5" {{ old('platform') == 'PS5' ? 'selected' : '' }}>PlayStation 5</option>
                        <option value="PS4" {{ old('platform') == 'PS4' ? 'selected' : '' }}>PlayStation 4</option>
                        <option value="PS3" {{ old('platform') == 'PS3' ? 'selected' : '' }}>PlayStation 3</option>
                        <option value="PSVITA" {{ old('platform') == 'PSVITA' ? 'selected' : '' }}>PS Vita</option>
                    </select>
                    @error('platform')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="image" class="form-label">Cover Image (optional)</label>
                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp">
                    @error('image')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                    <small class="form-text" style="color: #666;">Max 2MB. Formats: JPG, PNG, WebP.</small>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="price" class="form-label">Price (optional)</label>
                    <div style="position: relative; max-width: 200px;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #666;">€</span>
                        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" step="0.01" min="0" placeholder="0.00" style="padding-left: 28px;">
                    </div>
                    @error('price')
                        <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                    <small class="form-text" style="color: #666;">What did you pay for this game?</small>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus" style="margin-right: 6px;"></i> Add Game
                    </button>
                    <a href="{{ route('playstation.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
