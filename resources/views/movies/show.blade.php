@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $movie->title }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('movies.index') }}">Movies</a></li>
                    <li>{{ $movie->title }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="openWatchModal()" class="btn btn-primary">
                    <i class="fas fa-eye"></i>
                    Add Watch
                </button>
                <button onclick="deleteMovie()" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
                @if($movie->poster_url)
                    <div>
                        <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" style="width: 100%; border-radius: 0.5rem;">
                    </div>
                @endif
                <div>
                    <h2 style="margin-bottom: 1rem;">{{ $movie->title }}</h2>

                    @if($movie->overview)
                        <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 1.5rem;">{{ $movie->overview }}</p>
                    @endif

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        @if($movie->release_date)
                            <div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Release Date</div>
                                <div style="font-weight: 500;">{{ $movie->release_date->format('F d, Y') }}</div>
                            </div>
                        @endif

                        @if($movie->runtime)
                            <div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Runtime</div>
                                <div style="font-weight: 500;">{{ floor($movie->runtime / 60) }}h {{ $movie->runtime % 60 }}m</div>
                            </div>
                        @endif

                        @if($movie->vote_average)
                            <div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Rating</div>
                                <div style="font-weight: 500;">
                                    <i class="fas fa-star" style="color: #fbbf24;"></i>
                                    {{ number_format($movie->vote_average, 1) }}/10
                                </div>
                            </div>
                        @endif

                        <div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Times Watched</div>
                            <div style="font-weight: 500;">{{ $movie->watch_count }}x</div>
                        </div>
                    </div>

                    @if($movie->genres && count($movie->genres) > 0)
                        <div style="margin-bottom: 1.5rem;">
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Genres</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                @foreach($movie->genres as $genre)
                                    <span style="padding: 0.25rem 0.75rem; background: #374151; border-radius: 9999px; font-size: 0.875rem; color: #f3f4f6;">
                                        {{ $genre }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($movie->last_watched_at)
                        <div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.25rem;">Last Watched</div>
                            <div style="font-weight: 500;">{{ $movie->last_watched_at->format('F d, Y') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($movie->watches->isNotEmpty())
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Watch History</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($movie->watches()->orderBy('watched_at', 'desc')->get() as $watch)
                        <div style="padding: 1rem; background: #1f2937; border-radius: 0.5rem; display: flex; justify-content: space-between; align-items: center; color: #f3f4f6;">
                            <div>
                                <div style="font-weight: 500;">Watched</div>
                                <div style="color: #9ca3af; font-size: 0.875rem;">{{ $watch->watched_at ? $watch->watched_at->format('F d, Y') : 'No date' }}</div>
                            </div>
                            <button onclick="deleteMovieWatch({{ $watch->id }})" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
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
            <p style="margin-bottom: 1rem; color: #9ca3af;">{{ $movie->title }}</p>
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
function openWatchModal() {
    // Clear the date field (optional)
    document.getElementById('watchedAt').value = '';

    const modal = document.getElementById('watchModal');
    modal.classList.add('active');
}

function closeWatchModal() {
    const modal = document.getElementById('watchModal');
    modal.classList.remove('active');
}

function submitWatch() {
    const watchedAt = document.getElementById('watchedAt').value;

    fetch('{{ route('movies.watch', $movie->id) }}', {
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
            setTimeout(() => window.location.reload(), 500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add watch', 'error');
    });
}

function deleteMovieWatch(watchId) {
    if (!confirm('Delete this watch entry?')) {
        return;
    }

    fetch(`/movies/watches/${watchId}`, {
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

function deleteMovie() {
    if (!confirm('Are you sure you want to delete "{{ $movie->title }}"? This will also delete all watch history.')) {
        return;
    }

    fetch('{{ route('movies.destroy', $movie->id) }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Movie deleted successfully!');
            setTimeout(() => window.location.href = '{{ route('movies.index') }}', 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete movie', 'error');
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
