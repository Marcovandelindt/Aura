<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('APP.NAME', 'Aura') }}</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('styles')
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
                    alt="{{ $globalCurrentlyPlaying->album_name }}" class="floating-album-art">
                @endif

                <div class="floating-track-info">
                    <div class="floating-track-name">{{ $globalCurrentlyPlaying->track_name }}</div>
                    <div class="floating-artist-name">
                        @foreach($globalCurrentlyPlaying->artist_names as $index => $artistName)
                        <a href="{{ route('artists.show', ['artist' => urlencode($artistName)]) }}"
                            style="color: inherit; text-decoration: none; transition: color 0.2s;"
                            onmouseover="this.style.color='#1DB954'" onmouseout="this.style.color='#888'">{{ $artistName
                            }}</a>@if($index < count($globalCurrentlyPlaying->artist_names) - 1), @endif
                            @endforeach
                    </div>
                </div>

                <div class="floating-controls">
                    <button class="playback-control" onclick="skipToPrevious()" title="Previous track">
                        <i class="fas fa-step-backward"></i>
                    </button>

                    <button class="playback-control play-pause-btn" onclick="togglePlayPause()"
                        title="{{ $globalCurrentlyPlaying->is_playing ? 'Pause' : 'Play' }}">
                        <i class="fas fa-play play-icon"
                            style="{{ $globalCurrentlyPlaying->is_playing ? 'display: none;' : '' }}"></i>
                        <i class="fas fa-pause pause-icon"
                            style="{{ $globalCurrentlyPlaying->is_playing ? '' : 'display: none;' }}"></i>
                    </button>

                    <button class="playback-control" onclick="skipToNext()" title="Next track">
                        <i class="fas fa-step-forward"></i>
                    </button>

                    @if($globalCurrentlyPlaying->external_url)
                    <a href="{{ $globalCurrentlyPlaying->external_url }}" target="_blank" class="floating-spotify-link"
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

    <!-- Mood Popup HTML -->
    <div class="mood-popup-overlay" id="moodPopup">
        <div class="mood-popup">
            <div class="mood-popup-header">
                <div class="track-info">
                    <div class="track-name" id="moodTrackName"></div>
                    <div class="track-artist" id="moodTrackArtist"></div>
                </div>
                <button class="close-btn" onclick="closeMoodPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mood-popup-content">
                <!-- Current moods -->
                <div class="current-moods">
                    <h3><i class="fas fa-tags"></i> Current Moods</h3>
                    <div class="mood-tags" id="currentMoods">
                        <div class="empty-state">No moods assigned yet</div>
                    </div>
                </div>

                <!-- Available moods -->
                <div class="available-moods">
                    <h3><i class="fas fa-palette"></i> Add Moods</h3>
                    <div class="mood-grid" id="availableMoods">
                        <!-- Populated via JavaScript -->
                    </div>
                </div>

                <!-- Create new mood -->
                <div class="create-mood-section">
                    <button class="create-mood-toggle" onclick="toggleCreateMoodForm()">
                        <i class="fas fa-plus"></i> Create New Mood
                    </button>
                    <div class="create-mood-form" id="createMoodForm">
                        <div class="form-group">
                            <label for="moodName">Mood Name</label>
                            <input type="text" id="moodName" placeholder="e.g., Energetic, Chill, Christmas">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="moodIcon">Icon (FontAwesome class)</label>
                                <input type="text" id="moodIcon" placeholder="e.g., fas fa-bolt">
                            </div>
                            <div class="form-group">
                                <label for="moodColor">Color</label>
                                <input type="color" id="moodColor" value="#6366f1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="moodDescription">Description (optional)</label>
                            <input type="text" id="moodDescription" placeholder="Brief description of this mood">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-primary" onclick="createNewMood()">Create</button>
                            <button type="button" class="btn-secondary" onclick="cancelCreateMood()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mood Popup JavaScript -->
    <script>
        let currentTrackId = null;

        // Open mood popup for a track
        async function openMoodPopup(spotifyTrackId, trackName, trackArtist) {
            currentTrackId = spotifyTrackId;

            // Update track info in popup
            document.getElementById('moodTrackName').textContent = trackName;
            document.getElementById('moodTrackArtist').textContent = trackArtist;

            // Show popup
            document.getElementById('moodPopup').classList.add('active');

            // Load mood data
            await loadTrackMoods(spotifyTrackId);
        }
        
        // Close mood popup
        function closeMoodPopup() {
            document.getElementById('moodPopup').classList.remove('active');
            currentTrackId = null;
        }
        
        // Close popup when clicking overlay
        document.getElementById('moodPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMoodPopup();
            }
        });
        
        // Load moods for current track
        async function loadTrackMoods(spotifyTrackId) {
            try {
                const response = await fetch(`{{ route('moods.track') }}?spotify_track_id=${spotifyTrackId}`);
                const data = await response.json();

                if (data.success) {
                    displayCurrentMoods(data.track_moods, data.track_mood_games);
                    displayAvailableMoods(data.all_moods, data.track_moods);
                } else {
                    console.error('Failed to load moods:', data.message);
                }
            } catch (error) {
                console.error('Error loading moods:', error);
            }
        }
        
        // Display current moods
        function displayCurrentMoods(trackMoods, trackMoodGames) {
            const container = document.getElementById('currentMoods');

            if (trackMoods.length === 0) {
                container.innerHTML = '<div class="empty-state">No moods assigned yet</div>';
                return;
            }

            container.innerHTML = trackMoods.map(mood => {
                const games = trackMoodGames && trackMoodGames[mood.id] ? trackMoodGames[mood.id] : [];
                const gameLinks = games.map(game => `
                    <a href="{{ url('games') }}/${game.slug}" class="mood-game-link" title="View ${game.name}">
                        <i class="fas fa-gamepad"></i> ${game.name}
                    </a>
                `).join('');

                return `
                    <div class="mood-tag assigned" style="background-color: ${mood.color};">
                        <i class="${mood.icon} mood-icon"></i>
                        <span>${mood.name}</span>
                        ${gameLinks}
                        <button class="remove-mood" onclick="removeMoodFromTrack(${mood.id})" title="Remove mood">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }).join('');
        }
        
        // Display available moods
        function displayAvailableMoods(allMoods, trackMoods) {
            const container = document.getElementById('availableMoods');
            const assignedMoodIds = trackMoods.map(mood => mood.id);
            
            const availableMoods = allMoods.filter(mood => !assignedMoodIds.includes(mood.id));
            
            container.innerHTML = availableMoods.map(mood => `
                <button class="mood-tag available" 
                        style="--mood-color: ${mood.color};"
                        onclick="addMoodToTrack(${mood.id})"
                        title="${mood.description || ''}">
                    <i class="${mood.icon} mood-icon"></i>
                    <span>${mood.name}</span>
                </button>
            `).join('');
        }
        
        // Add mood to track
        async function addMoodToTrack(moodId) {
            if (!currentTrackId) return;

            try {
                const response = await fetch('{{ route('moods.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        spotify_track_id: currentTrackId,
                        mood_id: moodId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload moods to update display
                    await loadTrackMoods(currentTrackId);

                    // Update mood trigger buttons on page
                    updateMoodTriggerButtons(currentTrackId);

                    // Reload track game section if on track detail page
                    if (typeof loadTrackGames === 'function') {
                        await loadTrackGames();
                    }
                } else {
                    console.error('Failed to add mood:', data.message);
                }
            } catch (error) {
                console.error('Error adding mood:', error);
            }
        }
        
        // Remove mood from track
        async function removeMoodFromTrack(moodId) {
            if (!currentTrackId) return;

            try {
                const response = await fetch('{{ route('moods.remove') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        spotify_track_id: currentTrackId,
                        mood_id: moodId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload moods to update display
                    await loadTrackMoods(currentTrackId);

                    // Update mood trigger buttons on page
                    updateMoodTriggerButtons(currentTrackId);

                    // Reload track game section if on track detail page
                    if (typeof loadTrackGames === 'function') {
                        await loadTrackGames();
                    }
                } else {
                    console.error('Failed to remove mood:', data.message);
                }
            } catch (error) {
                console.error('Error removing mood:', error);
            }
        }
        
        // Update mood trigger buttons and mood pills to show current moods
        async function updateMoodTriggerButtons(spotifyTrackId) {
            try {
                const response = await fetch(`{{ route('moods.track') }}?spotify_track_id=${spotifyTrackId}`);
                const data = await response.json();
                
                if (data.success) {
                    // Update mood trigger buttons
                    const buttons = document.querySelectorAll(`[data-track-id="${spotifyTrackId}"]`);
                    buttons.forEach(button => {
                        if (data.track_moods.length > 0) {
                            button.classList.add('has-moods');
                            button.innerHTML = '<i class="fas fa-tags"></i>';
                            button.title = 'Manage moods';
                        } else {
                            button.classList.remove('has-moods');
                            button.innerHTML = '<i class="fas fa-plus"></i>';
                            button.title = 'Add mood to track';
                        }
                    });
                    
                    // Update the label text on track detail page
                    const trackDetailLabels = document.querySelectorAll(`button[data-track-id="${spotifyTrackId}"] + span`);
                    trackDetailLabels.forEach(label => {
                        if (data.track_moods.length > 0) {
                            label.textContent = 'Manage mood tags';
                        } else {
                            label.textContent = 'Add mood tags';
                        }
                    });
                    
                    // Update mood pills throughout the page
                    updateMoodPillsForTrack(spotifyTrackId, data.track_moods);
                }
            } catch (error) {
                console.error('Error updating mood triggers:', error);
            }
        }
        
        // Update mood pills for a specific track
        function updateMoodPillsForTrack(spotifyTrackId, trackMoods) {
            // Find all mood trigger buttons for this track
            const trackButtons = document.querySelectorAll(`[data-track-id="${spotifyTrackId}"]`);
            
            trackButtons.forEach(button => {
                // Find the track row/container that contains this button
                const trackRow = button.closest('tr');
                const trackContainer = button.closest('.top-list-item, .item-info');
                const trackElement = trackRow || trackContainer || button.closest('div');
                
                if (trackElement) {
                    // Find existing mood pills container in this track element
                    let moodPillsContainer = trackElement.querySelector('.mood-pills, .mood-pills-compact');
                    
                    if (trackMoods.length > 0) {
                        // Generate mood pills HTML
                        const isCompact = trackRow !== null; // Use compact in table rows
                        
                        const pillsHtml = trackMoods.map(mood => 
                            `<span class="mood-pill" style="background-color: ${mood.color};" title="${mood.name}">
                                <i class="${mood.icon} mood-icon"></i>
                                <span class="mood-text">${mood.name}</span>
                            </span>`
                        ).join('');
                        
                        if (moodPillsContainer) {
                            // Update existing container
                            moodPillsContainer.innerHTML = pillsHtml;
                        } else {
                            // Create new mood pills container
                            const containerClass = isCompact ? 'mood-pills-compact' : 'mood-pills';
                            const newContainer = document.createElement('span');
                            newContainer.className = containerClass;
                            newContainer.innerHTML = pillsHtml;
                            
                            // Find the best place to insert mood pills
                            let insertTarget = null;
                            
                            if (trackRow) {
                                // In table: find track name cell
                                const trackNameCell = trackRow.querySelector('td strong, td .item-link');
                                insertTarget = trackNameCell;
                            } else if (trackContainer) {
                                // In list: find track name link  
                                const trackNameLink = trackContainer.querySelector('a, .track-name');
                                insertTarget = trackNameLink;
                            }
                            
                            if (insertTarget) {
                                // Insert after track name, in the same container as the track name
                                if (trackRow) {
                                    // In tables: add to the same TD cell after the link/strong element
                                    const trackCell = insertTarget.closest('td');
                                    if (trackCell) {
                                        trackCell.appendChild(newContainer);
                                    }
                                } else {
                                    // In lists: add to the same container after the link
                                    insertTarget.parentNode.appendChild(newContainer);
                                }
                            }
                        }
                    } else {
                        // Remove mood pills if no moods
                        if (moodPillsContainer) {
                            moodPillsContainer.remove();
                        }
                    }
                }
            });
        }
        
        // Toggle create mood form
        function toggleCreateMoodForm() {
            const form = document.getElementById('createMoodForm');
            form.classList.toggle('active');
            
            if (form.classList.contains('active')) {
                document.getElementById('moodName').focus();
            } else {
                cancelCreateMood();
            }
        }
        
        // Cancel create mood
        function cancelCreateMood() {
            const form = document.getElementById('createMoodForm');
            form.classList.remove('active');
            
            // Reset form
            document.getElementById('moodName').value = '';
            document.getElementById('moodIcon').value = '';
            document.getElementById('moodColor').value = '#6366f1';
            document.getElementById('moodDescription').value = '';
        }
        
        // Create new mood
        async function createNewMood() {
            const name = document.getElementById('moodName').value.trim();
            const icon = document.getElementById('moodIcon').value.trim();
            const color = document.getElementById('moodColor').value;
            const description = document.getElementById('moodDescription').value.trim();
            
            if (!name || !icon) {
                alert('Please provide at least a name and icon for the mood.');
                return;
            }
            
            try {
                const response = await fetch('{{ route('moods.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        icon: icon,
                        color: color,
                        description: description
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload moods to show new mood
                    await loadTrackMoods(currentTrackId);
                    
                    // Close create form
                    cancelCreateMood();
                } else {
                    alert('Failed to create mood: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating mood:', error);
                alert('Error creating mood. Please try again.');
            }
        }
    </script>

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

    @stack('scripts')
</body>

</html>