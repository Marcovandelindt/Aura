<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>{{ config('APP.NAME', 'Aura') }}</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
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
                        <div class="floating-artist-name">{{ implode(', ', $globalCurrentlyPlaying->artist_names) }}</div>
                    </div>
                    
                    <div class="floating-controls">
                        @if($globalCurrentlyPlaying->is_playing)
                            <div class="play-indicator">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#1DB954">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        @else
                            <div class="pause-indicator">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="#888">
                                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                                </svg>
                            </div>
                        @endif
                        
                        @if($globalCurrentlyPlaying->external_url)
                            <a href="{{ $globalCurrentlyPlaying->external_url }}" 
                               target="_blank" 
                               class="floating-spotify-link"
                               title="Open in Spotify">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1DB954">
                                    <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                                </svg>
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
</body>

</html>