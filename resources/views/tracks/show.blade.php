@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <a href="{{ route('music.index') }}" class="back-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                        </svg>
                        Back to Music
                    </a>
                </div>
                <h1>{{ $track->track_name }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>{{ $track->track_name }}</li>
                </ul>
            </div>
            @if($track->external_url ?? $track->spotify_uri)
                <div>
                    <a href="{{ $track->external_url ?? 'https://open.spotify.com/track/' . str_replace('spotify:track:', '', $track->spotify_uri) }}" 
                       target="_blank" 
                       class="btn btn-primary"
                       style="display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                        </svg>
                        Open in Spotify
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Track Info Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <div style="display: flex; align-items: flex-start; gap: 2rem;">
                @if($track->album_image_url)
                    <img src="{{ $track->album_image_url }}" 
                         alt="{{ $track->album_name }}"
                         style="width: 200px; height: 200px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); flex-shrink: 0;">
                @endif
                
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 2rem; font-weight: 700;">{{ $track->track_name }}</h2>
                    <x-mood-pills :moods="$track->moods ?? []" />
                    <p style="margin: 0 0 1rem 0; font-size: 1.25rem; color: #666;">by {{ $track->artists_string }}</p>
                    <p style="margin: 0 0 1rem 0; color: #888;">from <strong>{{ $track->album_name }}</strong></p>
                    
                    <div style="display: flex; flex-wrap: gap: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #666;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12,6 12,12 16,14"/>
                            </svg>
                            <span>{{ $track->formatted_duration }}</span>
                        </div>
                        
                        @if($track->popularity)
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #666;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                                </svg>
                                <span>{{ $track->popularity }}% popularity</span>
                            </div>
                        @endif
                        
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #666;">
                            <button class="mood-trigger{{ !empty($track->moods) ? ' has-moods' : '' }}" 
                                    data-track-id="{{ $track->spotify_track_id }}"
                                    onclick="openMoodPopup('{{ $track->spotify_track_id }}', '{{ addslashes($track->track_name) }}', '{{ addslashes($track->artists_string) }}')"
                                    title="{{ !empty($track->moods) ? 'Manage moods' : 'Add mood to track' }}"
                                    style="margin: 0;">
                                <i class="fas fa-{{ !empty($track->moods) ? 'tags' : 'plus' }}"></i>
                            </button>
                            <span>{{ !empty($track->moods) ? 'Manage mood tags' : 'Add mood tags' }}</span>
                        </div>
                    </div>
                    
                    @if($track->genres && count($track->genres) > 0)
                        <div style="margin-bottom: 1rem;">
                            <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; color: #666;">Genres</h4>
                            <div style="display: flex; flex-wrap: gap: 0.5rem;">
                                @foreach($track->genres as $genre)
                                    <span style="background: linear-gradient(135deg, #1DB954 0%, #1ed760 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">
                                        {{ ucfirst($genre) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Game Management Section (only visible if track has "Game" mood) -->
    <div id="gameManagementSection" style="display: none; margin-bottom: 2rem;">
        <div class="card game-management-card">
            <div class="card-header game-management-header">
                <div class="game-management-header-content">
                    <div class="game-management-title">
                        <i class="fas fa-gamepad"></i>
                        <div>
                            <h3>Game Soundtrack</h3>
                            <p>Assign this track to a game</p>
                        </div>
                    </div>
                    <button onclick="openGameModal()" class="btn btn-light game-assign-btn">
                        <i class="fas fa-plus"></i> Assign Game
                    </button>
                </div>
            </div>
            <div class="card-body" id="assignedGamesContainer">
                <div class="empty-state">
                    <i class="fas fa-gamepad"></i>
                    <p>No game assigned yet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['total_plays'] }}</h3>
                        <p class="stat-label">Total Plays</p>
                        <span class="stat-change">All time</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['plays_this_week'] }}</h3>
                        <p class="stat-label">This Week</p>
                        <span class="stat-change">{{ $stats['plays_this_month'] }} this month</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['first_played'] ? $stats['first_played']->setTimezone('Europe/Amsterdam')->format('M j') : 'N/A' }}</h3>
                        <p class="stat-label">First Played</p>
                        <span class="stat-change">{{ $stats['first_played'] ? $stats['first_played']->setTimezone('Europe/Amsterdam')->format('H:i') . ' - ' . $stats['first_played']->diffForHumans() : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['last_played'] ? $stats['last_played']->setTimezone('Europe/Amsterdam')->format('M j') : 'N/A' }}</h3>
                        <p class="stat-label">Last Played</p>
                        <span class="stat-change">{{ $stats['last_played'] ? $stats['last_played']->setTimezone('Europe/Amsterdam')->format('H:i') . ' - ' . $stats['last_played']->diffForHumans() : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Play History -->
    <div class="card">
        <div class="card-header">
            <h3>Play History</h3>
        </div>
        <div class="card-body">
            @if($playedTracks->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Context</th>
                                <th>Time Ago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($playedTracks as $playedTrack)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $playedTrack->played_at->format('M j, Y') }}</strong>
                                            <br>
                                            <span style="color: #666; font-size: 0.875rem;">{{ $playedTrack->played_at->format('g:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($playedTrack->contexts)
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                @if($playedTrack->contexts['type'] === 'playlist')
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                    </svg>
                                                    <span style="color: #666;">Playlist</span>
                                                @elseif($playedTrack->contexts['type'] === 'album')
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <circle cx="12" cy="12" r="10"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                    <span style="color: #666;">Album</span>
                                                @else
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <circle cx="12" cy="12" r="1"/>
                                                        <circle cx="19" cy="12" r="1"/>
                                                        <circle cx="5" cy="12" r="1"/>
                                                    </svg>
                                                    <span style="color: #666;">{{ ucfirst($playedTrack->contexts['type'] ?? 'Unknown') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: #999;">-</span>
                                        @endif
                                    </td>
                                    <td style="color: #666;">{{ $playedTrack->played_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($playedTracks->hasPages())
                    <div style="margin-top: 1rem; display: flex; justify-content: center;">
                        {{ $playedTracks->links() }}
                    </div>
                @endif
            @else
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 1rem; color: #ccc;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p>No play history found for this track.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Game Assignment Modal -->
<div class="mood-popup-overlay" id="gameModal">
    <div class="mood-popup game-modal" onclick="event.stopPropagation()">
        <div class="mood-popup-header game-modal-header">
            <div class="track-info">
                <div class="track-name">
                    <i class="fas fa-gamepad"></i> Assign Game
                </div>
                <div class="track-artist">Select or create a game for this track</div>
            </div>
            <button class="close-btn" onclick="closeGameModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <div class="game-modal-section">
                <label for="gameSelectModal" class="game-modal-label">
                    <i class="fas fa-list"></i> Select Existing Game
                </label>
                <select id="gameSelectModal" class="game-modal-select">
                    <option value="">Choose a game...</option>
                </select>
            </div>

            <div class="game-modal-divider">
                <span>OR</span>
            </div>

            <div class="game-modal-section">
                <label for="newGameNameModal" class="game-modal-label">
                    <i class="fas fa-plus-circle"></i> Create New Game
                </label>
                <input type="text" id="newGameNameModal" class="game-modal-input" placeholder="Type new game name...">
                <small class="game-modal-hint">
                    <i class="fas fa-info-circle"></i> Enter the name of a game that's not in the list
                </small>
            </div>

            <div class="game-modal-actions">
                <button type="button" class="btn-secondary" onclick="closeGameModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn-primary" onclick="assignGame()">
                    <i class="fas fa-check"></i> Assign Game
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const trackId = '{{ $track->spotify_track_id }}';

    // Load track games on page load
    document.addEventListener('DOMContentLoaded', async function() {
        await loadTrackGames();
    });

    // Load track games
    async function loadTrackGames() {
        try {
            const response = await fetch(`{{ route('moods.track-games') }}?spotify_track_id=${trackId}`);
            const data = await response.json();

            if (data.success && data.has_game_mood) {
                // Show game management section
                document.getElementById('gameManagementSection').style.display = 'block';

                // Display assigned games
                const container = document.getElementById('assignedGamesContainer');
                if (data.assigned_games && data.assigned_games.length > 0) {
                    container.innerHTML = data.assigned_games.map(game => `
                        <div class="game-card-assigned">
                            ${game.cover_image ? `
                                <img src="${game.cover_image}" alt="${game.name}" class="game-cover">
                            ` : `
                                <div class="game-cover-placeholder">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                            `}
                            <div class="game-info">
                                <h4>${game.name}</h4>
                                ${game.developer ? `<p class="game-developer">${game.developer}</p>` : ''}
                                <a href="{{ url('games') }}/${game.slug}" class="game-link">
                                    <i class="fas fa-external-link-alt"></i> View Game
                                </a>
                            </div>
                            <button onclick="changeGame()" class="btn btn-secondary game-change-btn">
                                <i class="fas fa-edit"></i> Change
                            </button>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-gamepad"></i>
                            <p>No game assigned yet. Click "Assign Game" to add one.</p>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Error loading track games:', error);
        }
    }

    // Open game modal
    async function openGameModal() {
        try {
            const response = await fetch(`{{ route('moods.track-games') }}?spotify_track_id=${trackId}`);
            const data = await response.json();

            if (data.success) {
                // Populate game select
                const gameSelect = document.getElementById('gameSelectModal');
                gameSelect.innerHTML = '<option value="">Choose a game...</option>' +
                    data.all_games.map(game => `<option value="${game.id}">${game.name}</option>`).join('');

                // Reset new game input
                document.getElementById('newGameNameModal').value = '';

                // Show modal
                document.getElementById('gameModal').classList.add('active');
            }
        } catch (error) {
            console.error('Error loading games:', error);
        }
    }

    // Close game modal
    function closeGameModal() {
        document.getElementById('gameModal').classList.remove('active');
    }

    // Close modal when clicking overlay
    document.getElementById('gameModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeGameModal();
        }
    });

    // Change game (same as open modal)
    function changeGame() {
        openGameModal();
    }

    // Assign game
    async function assignGame() {
        const gameId = document.getElementById('gameSelectModal').value;
        const gameName = document.getElementById('newGameNameModal').value.trim();

        if (!gameId && !gameName) {
            alert('Please select a game or enter a new game name');
            return;
        }

        try {
            const response = await fetch('{{ route('moods.assign-game') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    spotify_track_id: trackId,
                    game_id: gameId || null,
                    game_name: gameName || null
                })
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                closeGameModal();

                // Reload games
                await loadTrackGames();

                // Show success notification
                showNotification('Game assigned successfully!', 'success');
            } else {
                showNotification(data.message || 'Failed to assign game', 'error');
            }
        } catch (error) {
            console.error('Error assigning game:', error);
            showNotification('Failed to assign game', 'error');
        }
    }

    // Show notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
</script>

<style>
    /* Game Modal Styles */
    .game-modal {
        max-width: 550px;
    }

    .game-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .game-modal-section {
        margin-bottom: 1.5rem;
    }

    .game-modal-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9375rem;
    }

    .game-modal-label i {
        margin-right: 0.5rem;
        color: #6366f1;
    }

    .game-modal-select,
    .game-modal-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
        background: white;
    }

    .game-modal-select:focus,
    .game-modal-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .game-modal-hint {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .game-modal-hint i {
        margin-right: 0.25rem;
    }

    .game-modal-divider {
        text-align: center;
        margin: 2rem 0;
        position: relative;
    }

    .game-modal-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e5e7eb;
    }

    .game-modal-divider span {
        position: relative;
        background: white;
        padding: 0 1rem;
        color: #9ca3af;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .game-modal-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 2rem;
        justify-content: flex-end;
    }

    .game-modal-actions button {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .game-modal-actions .btn-primary {
        background: #6366f1;
        color: white;
    }

    .game-modal-actions .btn-primary:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .game-modal-actions .btn-secondary {
        background: #f3f4f6;
        color: #4b5563;
    }

    .game-modal-actions .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Game Management Card */
    .game-management-card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
        border: none;
    }

    .game-management-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border: none;
    }

    .game-management-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .game-management-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .game-management-title > i {
        font-size: 2rem;
    }

    .game-management-title h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .game-management-title p {
        margin: 0;
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .game-assign-btn {
        background: white;
        color: #667eea;
        border: none;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .game-assign-btn:hover {
        background: #f9fafb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Game Card Assigned */
    .game-card-assigned {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 12px;
    }

    .game-cover {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .game-cover-placeholder {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .game-cover-placeholder i {
        font-size: 2.5rem;
        color: rgba(255, 255, 255, 0.5);
    }

    .game-info {
        flex: 1;
    }

    .game-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 1.25rem;
        color: #1f2937;
        font-weight: 600;
    }

    .game-developer {
        margin: 0 0 0.75rem 0;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .game-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #6366f1;
        text-decoration: none;
        font-size: 0.9375rem;
        font-weight: 500;
        transition: color 0.2s;
    }

    .game-link:hover {
        color: #4f46e5;
    }

    .game-change-btn {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #4b5563;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .game-change-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    /* Notification */
    .notification {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 10000;
    }

    .notification.show {
        opacity: 1;
        transform: translateY(0);
    }

    .notification-success {
        background: #10b981;
    }

    .notification-error {
        background: #ef4444;
    }

    .notification i {
        font-size: 1.25rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
        display: block;
    }

    .empty-state p {
        margin: 0;
        font-size: 0.9375rem;
    }
</style>

@endsection