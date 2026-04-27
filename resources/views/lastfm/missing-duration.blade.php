@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <a href="{{ route('lastfm.index') }}" class="back-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                        </svg>
                        Terug naar Last.fm
                    </a>
                </div>
                <h1>Tracks zonder duratie</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('lastfm.index') }}">Last.fm</a></li>
                    <li>Ontbrekende duraties</li>
                </ul>
            </div>
            <div>
                <span style="background: linear-gradient(135deg, #e8183f 0%, #c0002c 100%); color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">
                    {{ number_format($totalMissing) }} unieke tracks
                </span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
            <h3>Tracks zonder duratie</h3>
            <button class="btn btn-primary" onclick="fetchAllVisible()" id="fetch-all-btn" style="font-size: 0.8rem; padding: 6px 14px;">
                <i class="fas fa-play"></i> Alle {{ $tracks->count() }} op deze pagina ophalen
            </button>
        </div>
        <div class="card-body">
            @if($tracks->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50"></th>
                                <th>Track</th>
                                <th>Artiest</th>
                                <th>Album</th>
                                <th style="text-align: right;">Scrobbles</th>
                                <th width="80">Status</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tracks as $track)
                            <tr id="row-{{ $loop->index }}">
                                <td>
                                    @if($track->album_image_url)
                                        <img src="{{ $track->album_image_url }}"
                                             alt="{{ $track->album_name }}"
                                             style="width: 40px; height: 40px; border-radius: 4px;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 4px; background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-music" style="color: #9ca3af; font-size: 0.75rem;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('tracks.lastfm', ['artist' => urlencode($track->artist_name), 'track' => urlencode($track->track_name)]) }}"
                                       style="color: inherit; text-decoration: none;">
                                        <strong>{{ $track->track_name }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('artists.show', ['artist' => urlencode($track->artist_name)]) }}"
                                       style="color: #666; text-decoration: none; transition: color 0.2s;"
                                       onmouseover="this.style.color='#e8183f'"
                                       onmouseout="this.style.color='#666'">
                                        {{ $track->artist_name }}
                                    </a>
                                </td>
                                <td style="color: #666;">{{ $track->album_name ?? '—' }}</td>
                                <td style="text-align: right;">
                                    <span style="background: linear-gradient(135deg, #e8183f 0%, #c0002c 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                                        {{ $track->scrobble_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($track->enriched_at)
                                        <span class="status-badge status-not-found" title="Eerder geprobeerd, niet gevonden op Last.fm">
                                            Niet gevonden
                                        </span>
                                    @else
                                        <span class="status-badge status-pending">
                                            Nog niet geprobeerd
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-wrap"
                                         data-track="{{ $track->track_name }}"
                                         data-artist="{{ $track->artist_name }}"
                                         data-row="{{ $loop->index }}">
                                        <div class="action-buttons">
                                            <button class="fetch-duration-btn" onclick="fetchDuration(this.closest('.action-wrap'))" title="Ophalen via Last.fm">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="fetch-duration-btn manual-btn" onclick="showManualInput(this.closest('.action-wrap'))" title="Handmatig invullen">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                        </div>
                                        <div class="manual-input-wrap" style="display: none;">
                                            <input type="number" class="manual-minutes" min="0" max="99" placeholder="0" style="width: 38px;">
                                            <span style="font-size: 0.85rem; color: #6b7280;">:</span>
                                            <input type="number" class="manual-seconds" min="0" max="59" placeholder="00" style="width: 38px;">
                                            <button class="fetch-duration-btn" onclick="saveDuration(this.closest('.action-wrap'))" title="Opslaan" style="color: #16a34a; border-color: #16a34a;">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="fetch-duration-btn" onclick="hideManualInput(this.closest('.action-wrap'))" title="Annuleren">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($tracks->hasPages())
                    <div style="margin-top: 1rem;">
                        {{ $tracks->links() }}
                    </div>
                @endif
            @else
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #1DB954; margin-bottom: 1rem; display: block;"></i>
                    <p style="font-size: 1.1rem;">Alle tracks hebben een duratie!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.status-badge {
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 10px;
    white-space: nowrap;
}
.status-pending {
    background: #f3f4f6;
    color: #6b7280;
}
.status-not-found {
    background: #fef2f2;
    color: #ef4444;
}
.status-found {
    background: #f0fdf4;
    color: #16a34a;
}
.action-wrap {
    display: inline-flex;
    align-items: center;
}
.action-buttons {
    display: flex;
    gap: 4px;
}
.manual-input-wrap {
    display: flex;
    align-items: center;
    gap: 3px;
}
.manual-input-wrap input {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 3px 4px;
    font-size: 0.8rem;
    text-align: center;
    outline: none;
}
.manual-input-wrap input:focus {
    border-color: #6366f1;
}
.fetch-duration-btn {
    background: none;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    padding: 4px 8px;
    color: #6b7280;
    font-size: 0.75rem;
    transition: all 0.15s;
    white-space: nowrap;
}
.fetch-duration-btn:hover {
    border-color: #e8183f;
    color: #e8183f;
}
.fetch-duration-btn:disabled {
    cursor: default;
    opacity: 0.4;
}
.manual-btn:hover {
    border-color: #6366f1 !important;
    color: #6366f1 !important;
}
</style>

<script>
const CSRF = '{{ csrf_token() }}';
const ENRICH_URL = '{{ route('lastfm.enrich-track') }}';
const SET_DURATION_URL = '{{ route('lastfm.set-duration') }}';

function markRowDone(wrap, formatted) {
    const rowIndex = wrap.dataset.row;
    const row = document.getElementById('row-' + rowIndex);
    const statusCell = row.querySelector('.status-badge');
    if (statusCell) {
        statusCell.className = 'status-badge status-found';
        statusCell.textContent = formatted;
        statusCell.title = '';
    }
    setTimeout(() => row.style.opacity = '0.4', 800);
}

function fetchDuration(wrap) {
    const btns = wrap.querySelectorAll('.fetch-duration-btn');
    const track = wrap.dataset.track;
    const artist = wrap.dataset.artist;
    const searchBtn = wrap.querySelector('.action-buttons .fetch-duration-btn:first-child');

    btns.forEach(b => b.disabled = true);
    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(ENRICH_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ track_name: track, artist_name: artist })
    })
    .then(r => r.json())
    .then(data => {
        btns.forEach(b => b.disabled = false);
        if (data.success) {
            searchBtn.innerHTML = '<i class="fas fa-check" style="color:#16a34a"></i>';
            markRowDone(wrap, data.formatted);
        } else {
            searchBtn.innerHTML = '<i class="fas fa-search"></i>';
            const rowIndex = wrap.dataset.row;
            const statusCell = document.getElementById('row-' + rowIndex)?.querySelector('.status-badge');
            if (statusCell) {
                statusCell.className = 'status-badge status-not-found';
                statusCell.textContent = 'Niet gevonden';
                statusCell.title = data.message;
            }
        }
    })
    .catch(() => {
        btns.forEach(b => b.disabled = false);
        searchBtn.innerHTML = '<i class="fas fa-search"></i>';
    });
}

function showManualInput(wrap) {
    wrap.querySelector('.action-buttons').style.display = 'none';
    wrap.querySelector('.manual-input-wrap').style.display = 'flex';
    wrap.querySelector('.manual-minutes').focus();
}

function hideManualInput(wrap) {
    wrap.querySelector('.manual-input-wrap').style.display = 'none';
    wrap.querySelector('.action-buttons').style.display = 'flex';
    wrap.querySelector('.manual-minutes').value = '';
    wrap.querySelector('.manual-seconds').value = '';
}

function saveDuration(wrap) {
    const minutes = parseInt(wrap.querySelector('.manual-minutes').value) || 0;
    const seconds = parseInt(wrap.querySelector('.manual-seconds').value) || 0;

    if (minutes === 0 && seconds === 0) {
        wrap.querySelector('.manual-minutes').focus();
        return;
    }

    const saveBtn = wrap.querySelector('.manual-input-wrap .fetch-duration-btn:first-of-type');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(SET_DURATION_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ track_name: wrap.dataset.track, artist_name: wrap.dataset.artist, minutes, seconds })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            markRowDone(wrap, data.formatted);
        } else {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-check"></i>';
            alert(data.message);
        }
    })
    .catch(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-check"></i>';
    });
}

// Allow Tab between minutes and seconds, Enter to save
document.addEventListener('keydown', function(e) {
    if (e.target.classList.contains('manual-minutes') && e.key === 'Tab') {
        e.preventDefault();
        e.target.closest('.action-wrap').querySelector('.manual-seconds').focus();
    }
    if ((e.target.classList.contains('manual-minutes') || e.target.classList.contains('manual-seconds')) && e.key === 'Enter') {
        saveDuration(e.target.closest('.action-wrap'));
    }
});

function fetchAllVisible() {
    const allBtn = document.getElementById('fetch-all-btn');
    allBtn.disabled = true;
    allBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Bezig...';

    const wraps = document.querySelectorAll('.action-wrap');
    let delay = 0;

    wraps.forEach(wrap => {
        const searchBtn = wrap.querySelector('.action-buttons .fetch-duration-btn:first-child');
        if (searchBtn && !searchBtn.disabled) {
            setTimeout(() => fetchDuration(wrap), delay);
            delay += 600;
        }
    });

    setTimeout(() => {
        allBtn.disabled = false;
        allBtn.innerHTML = '<i class="fas fa-play"></i> Alle {{ $tracks->count() }} op deze pagina ophalen';
    }, delay + 1000);
}
</script>
@endsection
