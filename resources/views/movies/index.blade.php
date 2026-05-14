@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Movies</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Movies</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('movies.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i>
                    Statistics
                </a>
                <button onclick="openAddMovieModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Movie
                </button>
            </div>
        </div>
    </div>

    @if($movies->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-film" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">No movies yet</h3>
                <p style="color: #6b7280;">Start tracking your movie collection by adding your first movie.</p>
                <button onclick="openAddMovieModal()" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Add Your First Movie
                </button>
            </div>
        </div>
    @else
        <!-- Search Bar -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
                        <input
                            type="text"
                            id="movieSearchInput"
                            class="form-control"
                            placeholder="Search movies..."
                            style="padding-left: 2.5rem;"
                            oninput="filterMovies()"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="games-grid" id="moviesGrid">
            @foreach($movies as $movie)
                <a href="{{ route('movies.show', $movie->id) }}" class="game-card movie-card movie-item" data-title="{{ strtolower($movie->title) }}" data-original-title="{{ strtolower($movie->original_title ?? '') }}">
                    @if($movie->poster_url)
                        <div class="game-cover movie-poster">
                            <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                        </div>
                    @else
                        <div class="game-cover game-cover-placeholder">
                            <i class="fas fa-film"></i>
                        </div>
                    @endif
                    <div class="game-info">
                        <h3 class="game-title">{{ $movie->title }}</h3>
                        @if($movie->release_date)
                            <p class="game-developer">{{ $movie->release_date->format('Y') }}</p>
                        @endif
                        <div class="game-stats">
                            @if($movie->watch_count > 0)
                                <span class="game-stat">
                                    <i class="fas fa-eye"></i>
                                    {{ $movie->watch_count }}x watched
                                </span>
                            @endif
                            @if($movie->runtime)
                                <span class="game-stat">
                                    <i class="fas fa-clock"></i>
                                    {{ $movie->runtime }} min
                                </span>
                            @endif
                            @if($movie->vote_average)
                                <span class="game-stat">
                                    <i class="fas fa-star"></i>
                                    {{ number_format($movie->vote_average, 1) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Add Movie Modal -->
<div class="mood-popup-overlay" id="addMovieModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Add Movie</h3>
            <button class="mood-popup-close" onclick="closeAddMovieModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <div class="form-group">
                <label>Search for a movie</label>
                <input type="text" id="movieSearch" class="form-control" placeholder="Type movie title..." oninput="searchMovies()">
            </div>
            <div id="movieSearchResults" style="margin-top: 1rem;"></div>
        </div>
    </div>
</div>

<script>
let searchTimeout;

function filterMovies() {
    const searchTerm = document.getElementById('movieSearchInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.movie-item');
    let visibleCount = 0;

    items.forEach(item => {
        const title = item.getAttribute('data-title');
        const originalTitle = item.getAttribute('data-original-title');

        if (title.includes(searchTerm) || originalTitle.includes(searchTerm)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    let noResultsMsg = document.getElementById('noResultsMessage');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 3rem; color: #9ca3af;';
            noResultsMsg.innerHTML = '<i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i><p>No movies found matching your search.</p>';
            document.getElementById('moviesGrid').appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

function openAddMovieModal() {
    const modal = document.getElementById('addMovieModal');
    modal.classList.add('active');
    setTimeout(() => document.getElementById('movieSearch').focus(), 100);
}

function closeAddMovieModal() {
    const modal = document.getElementById('addMovieModal');
    modal.classList.remove('active');
    document.getElementById('movieSearch').value = '';
    document.getElementById('movieSearchResults').innerHTML = '';
}

function searchMovies() {
    const query = document.getElementById('movieSearch').value.trim();

    if (query.length < 2) {
        document.getElementById('movieSearchResults').innerHTML = '';
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetch('{{ route('movies.search') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ query })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.results);
            }
        })
        .catch(error => console.error('Error:', error));
    }, 500);
}

function displaySearchResults(results) {
    const container = document.getElementById('movieSearchResults');

    if (results.length === 0) {
        container.innerHTML = '<p style="color: #6b7280; text-align: center;">No movies found</p>';
        return;
    }

    container.innerHTML = results.map(movie => `
        <div class="search-result-item" onclick="addMovie(${movie.id})">
            <div style="display: flex; gap: 1rem; align-items: center;">
                ${movie.poster_path
                    ? `<img src="https://image.tmdb.org/t/p/w92${movie.poster_path}" style="width: 46px; height: 69px; border-radius: 4px; object-fit: cover;">`
                    : '<div style="width: 46px; height: 69px; background: #374151; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-film" style="color: #6b7280;"></i></div>'}
                <div>
                    <div style="font-weight: 500; color: #f3f4f6;">${movie.title}</div>
                    ${movie.release_date ? `<div style="font-size: 0.875rem; color: #6b7280;">${movie.release_date.substring(0, 4)}</div>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

function addMovie(tmdbId) {
    fetch('{{ route('movies.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ tmdb_id: tmdbId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/movies/${data.movie.id}`;
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add movie', 'error');
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

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.getElementById('addMovieModal').classList.contains('active')) {
        closeAddMovieModal();
    }
});

// Close modal on overlay click
document.getElementById('addMovieModal').addEventListener('click', (e) => {
    if (e.target.id === 'addMovieModal') {
        closeAddMovieModal();
    }
});
</script>

<style>
.search-result-item {
    padding: 0.75rem;
    border: 1px solid #374151;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
}

.search-result-item:hover {
    background: #374151;
    border-color: #4b5563;
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

.movie-poster img {
    object-fit: cover;
}
</style>
@endsection
