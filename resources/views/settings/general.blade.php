@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <h1>General Settings</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li>Settings</li>
                <li>General</li>
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('settings.general.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Health Settings -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-heartbeat" style="margin-right: 8px; color: #10b981;"></i> Health Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="health_step_goal">Daily Step Goal</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input
                            type="number"
                            id="health_step_goal"
                            name="health_step_goal"
                            class="form-control"
                            value="{{ old('health_step_goal', $settings['health_step_goal']) }}"
                            min="1000"
                            max="100000"
                            step="500"
                            style="max-width: 200px;"
                        >
                        <span style="color: var(--text-secondary);">steps per day</span>
                    </div>
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                        This goal is used to track your daily step achievement. Common goals: 5,000 (low), 7,500 (moderate), 10,000 (active), 12,500+ (very active)
                    </small>
                </div>

                <!-- Quick presets -->
                <div style="margin-top: 1rem;">
                    <span style="font-size: 0.875rem; color: var(--text-secondary); margin-right: 0.5rem;">Quick presets:</span>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('health_step_goal').value = 5000">5,000</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('health_step_goal').value = 7500">7,500</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('health_step_goal').value = 10000">10,000</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('health_step_goal').value = 12500">12,500</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('health_step_goal').value = 15000">15,000</button>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="card">
            <div class="card-body" style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save" style="margin-right: 0.5rem;"></i>
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
