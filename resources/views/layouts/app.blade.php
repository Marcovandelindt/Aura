<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>{{ config('APP.NAME', 'Aura') }}</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="{{ $themeClass }}">
    <div id="app-wrapper">
        @include('layouts.partials.sidebar')
        <div class="app-content">
            @yield('content')
        </div>
        
        <!-- Global Currently Playing Bar -->
        @if($globalCurrentlyPlaying)
            <div class="floating-player">
                <div class="floating-player-content">
                    @if($globalCurrentlyPlaying->album_image_url)
                        <img src="{{ $globalCurrentlyPlaying->album_image_url }}" 
                             alt="{{ $globalCurrentlyPlaying->album_name }}"
                             class="floating-album-art">
                    @endif
                    
                    <div class="floating-track-info">
                        <div class="floating-track-name">{{ $globalCurrentlyPlaying->track_name }}</div>
                        <div class="floating-artist-name">
                            @foreach($globalCurrentlyPlaying->artist_names as $index => $artistName)
                                <a href="{{ route('artists.show', ['artist' => urlencode($artistName)]) }}" 
                                   style="color: inherit; text-decoration: none; transition: color 0.2s;"
                                   onmouseover="this.style.color='#1DB954'" 
                                   onmouseout="this.style.color='#888'">{{ $artistName }}</a>@if($index < count($globalCurrentlyPlaying->artist_names) - 1), @endif
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="floating-controls">
                        <button class="playback-control" onclick="skipToPrevious()" title="Previous track">
                            <i class="fas fa-step-backward"></i>
                        </button>
                        
                        <button class="playback-control play-pause-btn" onclick="togglePlayPause()" title="{{ $globalCurrentlyPlaying->is_playing ? 'Pause' : 'Play' }}">
                            <i class="fas fa-play play-icon" style="{{ $globalCurrentlyPlaying->is_playing ? 'display: none;' : '' }}"></i>
                            <i class="fas fa-pause pause-icon" style="{{ $globalCurrentlyPlaying->is_playing ? '' : 'display: none;' }}"></i>
                        </button>
                        
                        <button class="playback-control" onclick="skipToNext()" title="Next track">
                            <i class="fas fa-step-forward"></i>
                        </button>
                        
                        @if($globalCurrentlyPlaying->external_url)
                            <a href="{{ $globalCurrentlyPlaying->external_url }}" 
                               target="_blank" 
                               class="floating-spotify-link"
                               title="Open in Spotify">
                                <i class="fab fa-spotify" style="color: #1DB954;"></i>
                            </a>
                        @endif
                    </div>
                    
                    @if($globalCurrentlyPlaying->progress_ms && $globalCurrentlyPlaying->duration_ms)
                        @php
                            $progressPercent = ($globalCurrentlyPlaying->progress_ms / $globalCurrentlyPlaying->duration_ms) * 100;
                            $progressMinutes = floor($globalCurrentlyPlaying->progress_ms / 1000 / 60);
                            $progressSeconds = floor(($globalCurrentlyPlaying->progress_ms / 1000) % 60);
                            $durationMinutes = floor($globalCurrentlyPlaying->duration_ms / 1000 / 60);
                            $durationSeconds = floor(($globalCurrentlyPlaying->duration_ms / 1000) % 60);
                        @endphp
                        <div class="floating-progress">
                            <div class="floating-progress-fill" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <div class="floating-time">
                            <span class="current-time">{{ sprintf('%d:%02d', $progressMinutes, $progressSeconds) }}</span>
                            <span class="total-time">{{ sprintf('%d:%02d', $durationMinutes, $durationSeconds) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    @if($globalCurrentlyPlaying)
        <script>
            // CSRF Token for AJAX requests
            const csrfToken = '{{ csrf_token() }}';
            
            async function makePlaybackRequest(url) {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        console.error('Playback error:', data.message);
                        // Could add toast notification here
                        return false;
                    }
                    
                    return data;
                } catch (error) {
                    console.error('Network error:', error);
                    return false;
                }
            }
            
            async function togglePlayPause() {
                const result = await makePlaybackRequest('{{ route("spotify.playback.toggle") }}');
                
                if (result) {
                    // Toggle icons
                    const playIcon = document.querySelector('.play-icon');
                    const pauseIcon = document.querySelector('.pause-icon');
                    const playPauseBtn = document.querySelector('.play-pause-btn');
                    
                    if (result.is_playing) {
                        playIcon.style.display = 'none';
                        pauseIcon.style.display = 'block';
                        playPauseBtn.title = 'Pause';
                    } else {
                        playIcon.style.display = 'block';
                        pauseIcon.style.display = 'none';
                        playPauseBtn.title = 'Play';
                    }
                }
            }
            
            async function skipToNext() {
                const result = await makePlaybackRequest('{{ route("spotify.playback.next") }}');
                
                if (result) {
                    // Optionally refresh track info after a short delay
                    setTimeout(() => {
                        // Could refresh currently playing info here
                    }, 1000);
                }
            }
            
            async function skipToPrevious() {
                const result = await makePlaybackRequest('{{ route("spotify.playback.previous") }}');
                
                if (result) {
                    // Optionally refresh track info after a short delay
                    setTimeout(() => {
                        // Could refresh currently playing info here
                    }, 1000);
                }
            }
        </script>
    @endif
    
    <!-- Theme Switcher JavaScript -->
    <script>
        // Theme switching functionality
        async function switchTheme(theme) {
            try {
                const response = await fetch('{{ route("theme.switch") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ theme: theme })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update body class
                    document.body.className = data.themeClass;
                    
                    // Update active theme button
                    document.querySelectorAll('.theme-option').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelector(`[onclick="switchTheme('${theme}')"]`).classList.add('active');
                    
                    // Show success message (optional)
                    console.log(data.message);
                } else {
                    console.error('Theme switch failed:', data.message);
                }
            } catch (error) {
                console.error('Theme switch error:', error);
            }
        }
    </script>
</body>

</html>