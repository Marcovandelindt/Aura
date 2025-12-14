@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Steam Settings</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('steam.index') }}">Steam</a></li>
                    <li>Settings</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('steam.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="margin-top: 0;">Configuration</h3>
            <p>Add these settings to your <code>.env</code> file:</p>

            <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px; font-family: monospace; margin-bottom: 1.5rem;">
                <div style="margin-bottom: 0.5rem;">STEAM_API_KEY=your_api_key_here</div>
                <div>STEAM_ID=your_steam_id_here</div>
            </div>

            <h4>Current Status</h4>
            <table class="table" style="max-width: 400px;">
                <tr>
                    <td>API Key</td>
                    <td>
                        @if($apiKey)
                            <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Configured</span>
                        @else
                            <span style="color: #ef4444;"><i class="fas fa-times-circle"></i> Not configured</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Steam ID</td>
                    <td>
                        @if($steamId)
                            <span style="color: #10b981;"><i class="fas fa-check-circle"></i> {{ $steamId }}</span>
                        @else
                            <span style="color: #ef4444;"><i class="fas fa-times-circle"></i> Not configured</span>
                        @endif
                    </td>
                </tr>
            </table>

            @if($apiKey && $steamId)
            <div style="margin-top: 1.5rem;">
                <button type="button" id="test-connection" class="btn btn-secondary">
                    <i class="fas fa-plug" style="margin-right: 8px;"></i>
                    Test Connection
                </button>
                <span id="test-result" style="margin-left: 1rem;"></span>
            </div>
            @endif

            <hr style="margin: 2rem 0;">

            <h3>How to get your Steam API Key</h3>
            <ol>
                <li>Go to <a href="https://steamcommunity.com/dev/apikey" target="_blank">Steam API Key Registration</a></li>
                <li>Log in with your Steam account</li>
                <li>Enter a domain name (e.g., "localhost" for local development)</li>
                <li>Copy the API key and add it to your <code>.env</code> file</li>
            </ol>

            <h3>How to find your Steam ID</h3>
            <ol>
                <li>Go to your Steam profile</li>
                <li>If you have a custom URL, you can use this tool to get your Steam ID: <a href="https://steamid.io/" target="_blank">SteamID.io</a></li>
                <li>The Steam ID is a 17-digit number (e.g., 76561198012345678)</li>
                <li>Add it to your <code>.env</code> file</li>
            </ol>
        </div>
    </div>
</div>

@if($apiKey && $steamId)
<script>
document.getElementById('test-connection').addEventListener('click', async function() {
    const btn = this;
    const result = document.getElementById('test-result');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Testing...';
    result.textContent = '';

    try {
        const response = await fetch('{{ route('steam.test-connection') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            result.innerHTML = '<span style="color: #10b981;"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
        } else {
            result.innerHTML = '<span style="color: #ef4444;"><i class="fas fa-times-circle"></i> ' + data.error + '</span>';
        }
    } catch (error) {
        result.innerHTML = '<span style="color: #ef4444;"><i class="fas fa-times-circle"></i> Connection failed</span>';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-plug" style="margin-right: 8px;"></i> Test Connection';
});
</script>
@endif
@endsection
