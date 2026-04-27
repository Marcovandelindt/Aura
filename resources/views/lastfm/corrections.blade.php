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
                <h1>Artiest correcties</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('lastfm.index') }}">Last.fm</a></li>
                    <li>Correcties</li>
                </ul>
            </div>
            <div>
                <span style="background: linear-gradient(135deg, #e8183f 0%, #c0002c 100%); color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 500;">
                    {{ $corrections->count() }} correcties
                </span>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3>Track zoeken</h3>
        </div>
        <div class="card-body">
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                Zoek een specifieke track op naam om ontbrekende artiesten toe te voegen.
            </p>
            <div style="max-width: 480px;">
                <input type="text" id="track-search" placeholder="Zoek op tracknaam..." autocomplete="off"
                    style="width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>
            <div id="search-results" style="margin-top: 1rem;"></div>
        </div>
    </div>

    {{-- Top 100 tracks --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
            <h3>Top 100 meest gespeelde tracks</h3>
            <span style="font-size: 0.8rem; color: #6b7280;">Sorteer op scrobbles, corrigeer de collabs</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Track</th>
                            <th>Last.fm artiest</th>
                            <th style="text-align:right;">Scrobbles</th>
                            <th>Artiesten</th>
                            <th width="80"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topTracks as $i => $track)
                        <tr id="top-row-{{ $i }}" class="{{ $track['correction_id'] ? 'corrected-row' : '' }}">
                            <td style="color: #9ca3af; font-size: 0.8rem;">{{ $i + 1 }}</td>
                            <td><strong>{{ $track['track_name'] }}</strong></td>
                            <td style="color: #6b7280;">{{ $track['artist_name'] }}</td>
                            <td style="text-align:right;">
                                <span style="background: linear-gradient(135deg, #e8183f 0%, #c0002c 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                                    {{ $track['scrobble_count'] }}
                                </span>
                            </td>
                            <td>
                                <div class="artist-tag-input" onclick="focusTagInput(this)"
                                     id="tag-wrap-top-{{ $i }}">
                                    @foreach($track['all_artist_names'] as $artist)
                                        <span class="artist-tag-editable">{{ $artist }}<button type="button" onclick="removeTag(this)" title="Verwijderen">&times;</button></span>
                                    @endforeach
                                    <input type="text" class="tag-text-input" placeholder="+ artiest"
                                        data-track="{{ $track['track_name'] }}"
                                        data-artist="{{ $track['artist_name'] }}"
                                        onkeydown="handleTagKey(event, this)"
                                        onblur="handleTagBlur(event, this)">
                                </div>
                            </td>
                            <td>
                                <button class="fetch-duration-btn save-btn"
                                    style="color: #16a34a; border-color: #16a34a;"
                                    onclick="saveFromTop(this, {{ $i }}, '{{ addslashes($track['track_name']) }}', '{{ addslashes($track['artist_name']) }}', {{ $track['correction_id'] ?? 'null' }})">
                                    <i class="fas fa-check"></i> Opslaan
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Saved corrections --}}
    <div class="card">
        <div class="card-header">
            <h3>Opgeslagen correcties</h3>
        </div>
        <div class="card-body">
            @if($corrections->count() > 0)
                <div class="table-responsive">
                    <table class="table" id="corrections-table">
                        <thead>
                            <tr>
                                <th>Track</th>
                                <th>Last.fm artiest</th>
                                <th>Gecorrigeerde artiesten</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($corrections as $correction)
                            <tr id="correction-row-{{ $correction->id }}">
                                <td><strong>{{ $correction->track_name }}</strong></td>
                                <td style="color: #6b7280;">{{ $correction->artist_name }}</td>
                                <td>
                                    <div class="artist-tags">
                                        @foreach($correction->all_artist_names as $artist)
                                            <span class="artist-tag">{{ $artist }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <button class="fetch-duration-btn" onclick="deleteCorrection({{ $correction->id }})"
                                        title="Verwijderen" style="color: #ef4444; border-color: #ef4444;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 2rem; color: #6b7280;" id="empty-state">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #9ca3af; margin-bottom: 0.75rem; display: block;"></i>
                    <p>Nog geen correcties opgeslagen.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.corrected-row {
    background: #f0fdf4;
}
.artist-tag {
    display: inline-block;
    background: #f3f4f6;
    color: #374151;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    margin: 2px 2px 2px 0;
}
.artist-tag-input {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 4px 8px;
    min-width: 200px;
    cursor: text;
}
.artist-tag-input:focus-within {
    border-color: #6366f1;
}
.artist-tag-editable {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #e0e7ff;
    color: #3730a3;
    padding: 2px 6px 2px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
}
.artist-tag-editable button {
    background: none;
    border: none;
    cursor: pointer;
    color: #6366f1;
    padding: 0;
    line-height: 1;
    font-size: 0.75rem;
}
.tag-text-input {
    border: none;
    outline: none;
    font-size: 0.85rem;
    min-width: 80px;
    flex: 1;
    background: transparent;
}
.search-result-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    background: #fafafa;
}
.search-result-row.saved {
    border-color: #bbf7d0;
    background: #f0fdf4;
}
.fetch-duration-btn {
    background: none;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    padding: 4px 10px;
    color: #6b7280;
    font-size: 0.75rem;
    transition: all 0.15s;
    white-space: nowrap;
}
.fetch-duration-btn:hover {
    border-color: #e8183f;
    color: #e8183f;
}
.save-btn:hover {
    border-color: #16a34a !important;
    color: #16a34a !important;
}
</style>

<script>
const CSRF = '{{ csrf_token() }}';
const SEARCH_URL = '{{ route('lastfm.corrections.search') }}';
const SAVE_URL = '{{ route('lastfm.corrections.save') }}';
const DELETE_URL_BASE = '{{ url('lastfm/corrections') }}';

// Search
let searchTimer = null;
document.getElementById('track-search').addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('search-results').innerHTML = ''; return; }
    searchTimer = setTimeout(() => doSearch(q), 300);
});

function doSearch(q) {
    fetch(SEARCH_URL + '?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(results => renderSearchResults(results));
}

function renderSearchResults(results) {
    const container = document.getElementById('search-results');
    if (!results.length) {
        container.innerHTML = '<p style="color:#9ca3af;font-size:0.85rem;">Geen tracks gevonden.</p>';
        return;
    }
    container.innerHTML = results.map(t => {
        const saved = t.correction_id !== null;
        const key = encodeURIComponent(t.track_name + '|' + t.artist_name);
        return `
        <div class="search-result-row ${saved ? 'saved' : ''}" id="sresult-${key}">
            <div style="flex:1;min-width:0;">
                <strong style="display:block;">${escHtml(t.track_name)}</strong>
                <span style="color:#6b7280;font-size:0.8rem;">${escHtml(t.artist_name)} &bull; ${t.scrobble_count} scrobbles</span>
            </div>
            <div class="artist-tag-input" onclick="focusTagInput(this)" id="stag-wrap-${key}">
                ${t.all_artist_names.map(a => makeEditableTag(a)).join('')}
                <input type="text" class="tag-text-input" placeholder="+ artiest"
                    data-track="${escAttr(t.track_name)}" data-artist="${escAttr(t.artist_name)}"
                    onkeydown="handleTagKey(event, this)" onblur="handleTagBlur(event, this)">
            </div>
            <button class="fetch-duration-btn save-btn" style="color:#16a34a;border-color:#16a34a;"
                onclick="saveFromSearch('${escAttr(t.track_name)}', '${escAttr(t.artist_name)}', ${t.correction_id ?? 'null'})">
                <i class="fas fa-check"></i> Opslaan
            </button>
        </div>`;
    }).join('');
}

// Tag input helpers
function makeEditableTag(name) {
    return `<span class="artist-tag-editable">${escHtml(name)}<button type="button" onclick="removeTag(this)" title="Verwijderen">&times;</button></span>`;
}

function removeTag(btn) {
    btn.closest('.artist-tag-editable').remove();
}

function handleTagKey(e, input) {
    if ((e.key === 'Enter' || e.key === ',') && input.value.trim()) {
        e.preventDefault();
        addTag(input.closest('.artist-tag-input'), input.value.trim(), input);
        input.value = '';
    }
}

function handleTagBlur(e, input) {
    if (input.value.trim()) {
        addTag(input.closest('.artist-tag-input'), input.value.trim(), input);
        input.value = '';
    }
}

function addTag(wrap, name, input) {
    const span = document.createElement('span');
    span.className = 'artist-tag-editable';
    span.innerHTML = `${escHtml(name)}<button type="button" onclick="removeTag(this)" title="Verwijderen">&times;</button>`;
    wrap.insertBefore(span, input);
}

function focusTagInput(wrap) {
    wrap.querySelector('.tag-text-input')?.focus();
}

function getTagsFromWrap(wrap) {
    return Array.from(wrap.querySelectorAll('.artist-tag-editable'))
        .map(el => el.childNodes[0].textContent.trim())
        .filter(Boolean);
}

// Save from top 100 table
function saveFromTop(btn, index, trackName, artistName, correctionId) {
    const wrap = document.getElementById('tag-wrap-top-' + index);
    const artists = getTagsFromWrap(wrap);
    if (!artists.length) { alert('Voeg minimaal één artiest toe.'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ track_name: trackName, artist_name: artistName, all_artist_names: artists }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Opgeslagen';
        if (data.success) {
            document.getElementById('top-row-' + index)?.classList.add('corrected-row');
            if (correctionId) {
                updateCorrectionRow(correctionId, trackName, artistName, artists);
            } else {
                addCorrectionRow(data.id, trackName, artistName, artists);
            }
        }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Opslaan'; });
}

// Save from search results
function saveFromSearch(trackName, artistName, correctionId) {
    const key = encodeURIComponent(trackName + '|' + artistName);
    const wrap = document.getElementById('stag-wrap-' + key);
    const artists = getTagsFromWrap(wrap);
    if (!artists.length) { alert('Voeg minimaal één artiest toe.'); return; }

    fetch(SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ track_name: trackName, artist_name: artistName, all_artist_names: artists }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('sresult-' + key)?.classList.add('saved');
            if (correctionId) {
                updateCorrectionRow(correctionId, trackName, artistName, artists);
            } else {
                addCorrectionRow(data.id, trackName, artistName, artists);
            }
        }
    });
}

// Corrections table helpers
function addCorrectionRow(id, trackName, artistName, artists) {
    document.getElementById('empty-state')?.remove();

    let tbody = document.querySelector('#corrections-table tbody');
    if (!tbody) {
        document.querySelector('.card:last-child .card-body').innerHTML = `
            <div class="table-responsive">
                <table class="table" id="corrections-table">
                    <thead><tr><th>Track</th><th>Last.fm artiest</th><th>Gecorrigeerde artiesten</th><th width="50"></th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>`;
        tbody = document.querySelector('#corrections-table tbody');
    }

    const tr = document.createElement('tr');
    tr.id = 'correction-row-' + id;
    tr.innerHTML = `
        <td><strong>${escHtml(trackName)}</strong></td>
        <td style="color:#6b7280;">${escHtml(artistName)}</td>
        <td><div class="artist-tags">${artists.map(a => `<span class="artist-tag">${escHtml(a)}</span>`).join('')}</div></td>
        <td><button class="fetch-duration-btn" onclick="deleteCorrection(${id})" title="Verwijderen" style="color:#ef4444;border-color:#ef4444;"><i class="fas fa-trash"></i></button></td>`;
    tbody.appendChild(tr);
    updateCount(1);
}

function updateCorrectionRow(id, trackName, artistName, artists) {
    const row = document.getElementById('correction-row-' + id);
    if (!row) return;
    row.querySelector('.artist-tags').innerHTML = artists.map(a => `<span class="artist-tag">${escHtml(a)}</span>`).join('');
}

function deleteCorrection(id) {
    if (!confirm('Correctie verwijderen?')) return;
    fetch(DELETE_URL_BASE + '/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('correction-row-' + id)?.remove();
            updateCount(-1);
        }
    });
}

function updateCount(delta) {
    const badge = document.querySelector('.content-header span[style*="background"]');
    if (badge) {
        const match = badge.textContent.match(/\d+/);
        if (match) badge.textContent = (parseInt(match[0]) + delta) + ' correcties';
    }
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(str) {
    return String(str).replace(/'/g, "\\'");
}
</script>
@endsection
