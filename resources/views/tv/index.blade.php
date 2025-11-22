@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>TV Series</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>TV Series</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('tv.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i>
                    Statistics
                </a>
                <button onclick="openAddSeriesModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Series
                </button>
            </div>
        </div>
    </div>

    @if($series->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-tv" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">No TV series yet</h3>
                <p style="color: #6b7280;">Start tracking your TV series by adding your first show.</p>
                <button onclick="openAddSeriesModal()" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Add Your First Series
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
                            id="seriesSearchInput"
                            class="form-control"
                            placeholder="Search TV series..."
                            style="padding-left: 2.5rem;"
                            oninput="filterSeries()"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="games-grid" id="seriesGrid">
            @foreach($series as $show)
                <a href="{{ route('tv.show', $show->id) }}" class="game-card tv-card series-item" data-series-name="{{ strtolower($show->name) }}">
                    @if($show->poster_url)
                        <div class="game-cover movie-poster">
                            <img src="{{ $show->poster_url }}" alt="{{ $show->name }}">
                            @if($show->completion_percentage > 0)
                                <div class="completion-badge">{{ number_format($show->completion_percentage) }}%</div>
                            @endif
                        </div>
                    @else
                        <div class="game-cover game-cover-placeholder">
                            <i class="fas fa-tv"></i>
                        </div>
                    @endif
                    <div class="game-info">
                        <h3 class="game-title">{{ $show->name }}</h3>
                        @if($show->first_air_date)
                            <p class="game-developer">{{ $show->first_air_date->format('Y') }}</p>
                        @endif
                        <div class="game-stats">
                            <span class="game-stat">
                                <i class="fas fa-tv"></i>
                                {{ $show->episodes_watched }}/{{ $show->number_of_episodes }} episodes
                            </span>
                            @if($show->vote_average)
                                <span class="game-stat">
                                    <i class="fas fa-star"></i>
                                    {{ number_format($show->vote_average, 1) }}
                                </span>
                            @endif
                        </div>
                        @if($show->total_watches > 0)
                            <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                <span style="display: inline-block; padding: 0.25rem 0.5rem; background: #10b981; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                    {{ $show->total_watches }} plays
                                </span>
                                <span style="display: inline-block; padding: 0.25rem 0.5rem; background: #3b82f6; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                    {{ $show->total_hours }}h
                                </span>
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Add Series Modal -->
<div class="mood-popup-overlay" id="addSeriesModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Add TV Series</h3>
            <button class="mood-popup-close" onclick="closeAddSeriesModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <div class="form-group">
                <label>Search for a TV series</label>
                <input type="text" id="seriesSearch" class="form-control" placeholder="Type series title..." oninput="searchSeries()">
            </div>
            <div id="seriesSearchResults" style="margin-top: 1rem;"></div>
        </div>
    </div>
</div>

<script>
let searchTimeout;

function openAddSeriesModal() {
    const modal = document.getElementById('addSeriesModal');
    modal.classList.add('active');
    setTimeout(() => document.getElementById('seriesSearch').focus(), 100);
}

function closeAddSeriesModal() {
    const modal = document.getElementById('addSeriesModal');
    modal.classList.remove('active');
    document.getElementById('seriesSearch').value = '';
    document.getElementById('seriesSearchResults').innerHTML = '';
}

function searchSeries() {
    const query = document.getElementById('seriesSearch').value.trim();

    if (query.length < 2) {
        document.getElementById('seriesSearchResults').innerHTML = '';
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetch('{{ route('tv.search') }}', {
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
    const container = document.getElementById('seriesSearchResults');

    if (results.length === 0) {
        container.innerHTML = '<p style="color: #6b7280; text-align: center;">No series found</p>';
        return;
    }

    container.innerHTML = results.map(series => `
        <div class="search-result-item" onclick="addSeries(${series.id})">
            <div style="display: flex; gap: 1rem; align-items: center;">
                ${series.poster_path
                    ? `<img src="https://image.tmdb.org/t/p/w92${series.poster_path}" style="width: 46px; height: 69px; border-radius: 4px; object-fit: cover;">`
                    : '<div style="width: 46px; height: 69px; background: #374151; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-tv" style="color: #6b7280;"></i></div>'}
                <div>
                    <div style="font-weight: 500; color: #f3f4f6;">${series.name}</div>
                    ${series.first_air_date ? `<div style="font-size: 0.875rem; color: #6b7280;">${series.first_air_date.substring(0, 4)}</div>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

function addSeries(tmdbId) {
    const loadingMsg = showNotification('Adding series and fetching all episodes... This may take a moment.', 'info');

    fetch('{{ route('tv.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ tmdb_id: tmdbId })
    })
    .then(response => response.json())
    .then(data => {
        loadingMsg.remove();
        if (data.success) {
            showNotification('TV series added successfully!');
            closeAddSeriesModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        loadingMsg.remove();
        console.error('Error:', error);
        showNotification('Failed to add series', 'error');
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
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;

    document.body.appendChild(notification);
    if (type !== 'info') {
        setTimeout(() => notification.remove(), 3000);
    }
    return notification;
}

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.getElementById('addSeriesModal').classList.contains('active')) {
        closeAddSeriesModal();
    }
});

// Close modal on overlay click
document.getElementById('addSeriesModal').addEventListener('click', (e) => {
    if (e.target.id === 'addSeriesModal') {
        closeAddSeriesModal();
    }
});

// Filter series function
function filterSeries() {
    const searchInput = document.getElementById('seriesSearchInput');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const seriesItems = document.querySelectorAll('.series-item');
    let visibleCount = 0;

    seriesItems.forEach(item => {
        const seriesName = item.getAttribute('data-series-name');

        if (seriesName.includes(searchTerm)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show/hide no results message
    let noResultsMsg = document.getElementById('noResultsMessage');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 3rem; color: #9ca3af;';
            noResultsMsg.innerHTML = '<i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i><p>No series found matching your search.</p>';
            document.getElementById('seriesGrid').appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}
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

.completion-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(16, 185, 129, 0.9);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
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
