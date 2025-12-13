@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Add Nintendo Switch Game</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('nintendo-switch.index') }}">Nintendo Switch</a></li>
                    <li>Add Game</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('nintendo-switch.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('nintendo-switch.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="name" class="form-label">Game Name *</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                    @error('name')
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

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus" style="margin-right: 6px;"></i> Add Game
                    </button>
                    <a href="{{ route('nintendo-switch.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
