@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $series->name }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('tv.index') }}">TV Series</a></li>
                    <li>{{ $series->name }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="deleteSeries()" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
                @if($series->poster_url)
                    <div>
                        <img src="{{ $series->poster_url }}" alt="{{ $series->name }}" style="width: 100%; border-radius: 0.5rem;">
                    </div>
                @endif
                <div>
                    <h2 style="margin-bottom: 1rem;">{{ $series->name }}</h2>

                    @if($series->overview)
                        <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 1.5rem;">{{ $series->overview }}</p>
                    @endif

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        @if($series->first_air_date)
                            <div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">First Air Date</div>
                                <div style="font-weight: 500;">{{ $series->first_air_date->format('F d, Y') }}</div>
                            </div>
                        @endif

                        <div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Progress</div>
                            <div style="font-weight: 500;">{{ $series->episodes_watched }}/{{ $series->number_of_episodes }} episodes ({{ number_format($series->completion_percentage) }}%)</div>
                        </div>

                        @if($series->vote_average)
                            <div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Rating</div>
                                <div style="font-weight: 500;">
                                    <i class="fas fa-star" style="color: #fbbf24;"></i>
                                    {{ number_format($series->vote_average, 1) }}/10
                                </div>
                            </div>
                        @endif

                        <div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Status</div>
                            <div style="font-weight: 500;">{{ $series->status }}</div>
                        </div>
                    </div>

                    @if($series->genres && count($series->genres) > 0)
                        <div style="margin-bottom: 1.5rem;">
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Genres</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                @foreach($series->genres as $genre)
                                    <span style="padding: 0.25rem 0.75rem; background: #374151; border-radius: 9999px; font-size: 0.875rem; color: #f3f4f6;">
                                        {{ $genre }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($series->last_watched_at)
                        <div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Last Watched</div>
                            <div style="font-weight: 500;">{{ $series->last_watched_at->format('F d, Y') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Seasons and Episodes -->
    @foreach($series->seasons()->orderBy('season_number')->get() as $season)
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ $season->name }} ({{ $season->episodes_watched }}/{{ $season->episode_count }} watched)</h3>
                <button onclick="toggleSeason({{ $season->id }})" class="btn btn-sm btn-secondary">
                    <i class="fas fa-chevron-down" id="season-icon-{{ $season->id }}"></i>
                </button>
            </div>
            <div id="season-{{ $season->id }}" class="card-body" style="display: none;">
                <div style="display: grid; gap: 0.75rem;">
                    @foreach($season->episodes()->orderBy('episode_number')->get() as $episode)
                        <div class="episode-item" data-episode-id="{{ $episode->id }}">
                            <div style="display: flex; align-items: center; gap: 1rem; justify-content: space-between;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 500;">
                                        {{ $episode->episode_number }}. {{ $episode->name }}
                                        @if($episode->watch_count > 0)
                                            <span style="color: #10b981; font-size: 0.875rem;">({{ $episode->watch_count }}x)</span>
                                        @endif
                                    </div>
                                    @if($episode->overview)
                                        <div style="color: #9ca3af; font-size: 0.875rem; margin-top: 0.25rem;">
                                            {{ Str::limit($episode->overview, 150) }}
                                        </div>
                                    @endif
                                    <div style="color: #6b7280; font-size: 0.75rem; margin-top: 0.25rem;">
                                        @if($episode->air_date)
                                            {{ $episode->air_date->format('M d, Y') }}
                                        @endif
                                        @if($episode->runtime)
                                            • {{ $episode->runtime }} min
                                        @endif
                                    </div>
                                    @if($episode->watches->count() > 0)
                                        <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                            @foreach($episode->watches()->orderBy('watched_at', 'desc')->get() as $watch)
                                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.125rem 0.5rem; background: #374151; border-radius: 0.25rem; font-size: 0.75rem; color: #f3f4f6;">
                                                    {{ $watch->watched_at ? $watch->watched_at->format('M d, Y') : 'No date' }}
                                                    <button onclick="deleteWatch({{ $watch->id }}, event)" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0; margin-left: 0.25rem;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <button onclick="openWatchModal({{ $episode->id }}, '{{ addslashes($episode->name) }}')" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Add Watch
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Add Watch Modal -->
<div class="mood-popup-overlay" id="watchModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Add Watch</h3>
            <button class="mood-popup-close" onclick="closeWatchModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <p id="episodeTitle" style="margin-bottom: 1rem; color: #9ca3af;"></p>
            <div class="form-group">
                <label>Watched At <span style="color: #6b7280; font-size: 0.875rem;">(optional)</span></label>
                <input type="date" id="watchedAt" class="form-control">
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button onclick="submitWatch()" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-check"></i> Add Watch
                </button>
                <button onclick="closeWatchModal()" class="btn btn-secondary">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Restore season states on page load
document.addEventListener('DOMContentLoaded', function() {
    const openSeasons = JSON.parse(localStorage.getItem('openSeasons_{{ $series->id }}') || '[]');
    openSeasons.forEach(seasonId => {
        const content = document.getElementById(`season-${seasonId}`);
        const icon = document.getElementById(`season-icon-${seasonId}`);
        if (content && icon) {
            content.style.display = 'block';
            icon.className = 'fas fa-chevron-up';
        }
    });
});

function toggleSeason(seasonId) {
    const content = document.getElementById(`season-${seasonId}`);
    const icon = document.getElementById(`season-icon-${seasonId}`);
    const seriesId = {{ $series->id }};

    // Get current open seasons
    let openSeasons = JSON.parse(localStorage.getItem(`openSeasons_${seriesId}`) || '[]');

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
        // Add to open seasons
        if (!openSeasons.includes(seasonId)) {
            openSeasons.push(seasonId);
        }
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
        // Remove from open seasons
        openSeasons = openSeasons.filter(id => id !== seasonId);
    }

    // Save to localStorage
    localStorage.setItem(`openSeasons_${seriesId}`, JSON.stringify(openSeasons));
}

let currentEpisodeId = null;

function openWatchModal(episodeId, episodeName) {
    currentEpisodeId = episodeId;
    document.getElementById('episodeTitle').textContent = episodeName;

    // Clear the date field (optional)
    document.getElementById('watchedAt').value = '';

    const modal = document.getElementById('watchModal');
    modal.classList.add('active');
}

function closeWatchModal() {
    const modal = document.getElementById('watchModal');
    modal.classList.remove('active');
    currentEpisodeId = null;
}

function submitWatch() {
    const watchedAt = document.getElementById('watchedAt').value;

    fetch(`/tv/episodes/${currentEpisodeId}/watch`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ watched_at: watchedAt || null })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Watch added successfully!');
            closeWatchModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add watch', 'error');
    });
}

function deleteWatch(watchId, event) {
    event.stopPropagation();

    if (!confirm('Delete this watch entry?')) {
        return;
    }

    fetch(`/tv/watches/${watchId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Watch deleted successfully!');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete watch', 'error');
    });
}

function deleteSeries() {
    if (!confirm('Are you sure you want to delete "{{ $series->name }}"? This will also delete all watch history.')) {
        return;
    }

    fetch('{{ route('tv.destroy', $series->id) }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Series deleted successfully!');
            setTimeout(() => window.location.href = '{{ route('tv.index') }}', 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete series', 'error');
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>

<style>
.episode-item {
    padding: 1rem;
    background: #1f2937;
    border-radius: 0.5rem;
    transition: all 0.2s;
    flex-shrink: 0;
    color: #f3f4f6;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endsection
