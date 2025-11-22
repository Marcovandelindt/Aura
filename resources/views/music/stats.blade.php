@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Music Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('music.index') }}">Music</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('music.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Music
                </a>
            </div>
        </div>
    </div>
    
    <!-- Overall Statistics -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($stats['listening_stats']['total_tracks']) }}</h3>
                        <p class="stat-label">Total Tracks Played</p>
                        <span class="stat-change">{{ number_format($stats['listening_stats']['unique_tracks']) }} unique songs</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['listening_stats']['total_duration_hours'] }}</h3>
                        <p class="stat-label">Hours Listened</p>
                        <span class="stat-change">{{ $stats['listening_stats']['average_per_day'] }} tracks/day avg</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['listening_stats']['tracks_this_week'] }}</h3>
                        <p class="stat-label">This Week</p>
                        <span class="stat-change">{{ $stats['listening_stats']['tracks_today'] }} today</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['listening_stats']['tracks_this_month'] }}</h3>
                        <p class="stat-label">This Month</p>
                        @if($stats['listening_stats']['first_track_date'])
                            <span class="stat-change">Since {{ $stats['listening_stats']['first_track_date']->format('M j, Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Listening Times -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>🕐 Top Listening Times</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">When you listen to music most</p>
        </div>
        <div class="card-body">
            @if(count($stats['top_listening_times']) > 0)
                <div class="listening-times-grid">
                    @foreach($stats['top_listening_times'] as $index => $timeStats)
                        <div class="time-stat-card">
                            <div class="time-rank">{{ $index + 1 }}</div>
                            <div class="time-emoji">{{ $timeStats['emoji'] }}</div>
                            <div class="time-info">
                                <div class="time-label">{{ $timeStats['time_range'] }}</div>
                                <div class="time-description">{{ $timeStats['description'] }}</div>
                                <div class="time-count">{{ $timeStats['play_count'] }} tracks</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">Not enough data yet to determine listening patterns.</p>
            @endif
        </div>
    </div>

    <!-- Top Moods -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-heart"></i> Top Moods</h3>
            <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your most tagged moods</p>
        </div>
        <div class="card-body">
            @if(count($stats['top_moods']) > 0)
                <div class="moods-grid">
                    @foreach($stats['top_moods'] as $index => $mood)
                        <div class="mood-stat-card">
                            <div class="mood-rank">{{ $index + 1 }}</div>
                            <div class="mood-icon" style="background-color: {{ $mood['color'] }}20; color: {{ $mood['color'] }};">
                                <i class="{{ $mood['icon'] }}"></i>
                            </div>
                            <div class="mood-info">
                                <div class="mood-name">{{ $mood['name'] }}</div>
                                <div class="mood-count">{{ number_format($mood['play_count']) }} {{ $mood['play_count'] === 1 ? 'play' : 'plays' }} &bull; {{ $mood['track_count'] }} {{ $mood['track_count'] === 1 ? 'track' : 'tracks' }}</div>
                            </div>
                            <div class="mood-color-indicator" style="background-color: {{ $mood['color'] }};"></div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">No moods tagged yet. Add moods to your tracks to see statistics!</p>
            @endif
        </div>
    </div>

    <!-- Advanced Statistics -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Weekday vs Weekend -->
        <div class="card">
            <div class="card-header">
                <h3>📅 Weekday vs Weekend</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your listening patterns</p>
            </div>
            <div class="card-body">
                <div class="weekday-weekend-chart">
                    <div class="chart-item">
                        <div class="chart-bar weekday-bar" style="height: {{ $stats['weekday_vs_weekend']['weekday_percentage'] }}%;">
                            <span class="chart-value">{{ $stats['weekday_vs_weekend']['weekday_percentage'] }}%</span>
                        </div>
                        <div class="chart-label">
                            <strong>Weekdays</strong>
                            <span>{{ $stats['weekday_vs_weekend']['weekday_count'] }} plays</span>
                        </div>
                    </div>
                    <div class="chart-item">
                        <div class="chart-bar weekend-bar" style="height: {{ $stats['weekday_vs_weekend']['weekend_percentage'] }}%;">
                            <span class="chart-value">{{ $stats['weekday_vs_weekend']['weekend_percentage'] }}%</span>
                        </div>
                        <div class="chart-label">
                            <strong>Weekend</strong>
                            <span>{{ $stats['weekday_vs_weekend']['weekend_count'] }} plays</span>
                        </div>
                    </div>
                </div>
                <div class="preference-indicator">
                    @if($stats['weekday_vs_weekend']['preference'] === 'weekday')
                        <span class="preference weekday">🏢 You're a <strong>weekday listener</strong></span>
                    @elseif($stats['weekday_vs_weekend']['preference'] === 'weekend')
                        <span class="preference weekend">🎉 You're a <strong>weekend warrior</strong></span>
                    @else
                        <span class="preference equal">⚖️ <strong>Perfectly balanced</strong></span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Repeat Ratio -->
        <div class="card">
            <div class="card-header">
                <h3>🔄 Repeat Ratio</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Discovery vs comfort zones</p>
            </div>
            <div class="card-body">
                <div class="repeat-ratio-chart">
                    <div class="ratio-circle">
                        <div class="ratio-percentage">{{ $stats['repeat_ratio']['repeat_percentage'] }}%</div>
                        <div class="ratio-label">Repeats</div>
                    </div>
                    <div class="ratio-stats">
                        <div class="ratio-stat">
                            <span class="stat-number">{{ $stats['repeat_ratio']['discovery_percentage'] }}%</span>
                            <span class="stat-label">New Discovery</span>
                        </div>
                        <div class="ratio-stat">
                            <span class="stat-number">{{ number_format($stats['repeat_ratio']['unique_tracks']) }}</span>
                            <span class="stat-label">Unique Tracks</span>
                        </div>
                    </div>
                </div>
                @if($stats['repeat_ratio']['most_repeated_track'])
                    <div class="most-repeated">
                        <strong>Most repeated:</strong>
                        <span>{{ $stats['repeat_ratio']['most_repeated_track']['name'] }}</span>
                        <span class="repeat-count">({{ $stats['repeat_ratio']['most_repeated_track']['play_count'] }}x)</span>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Binge Sessions -->
        <div class="card">
            <div class="card-header">
                <h3>🎧 Binge Sessions</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Your longest listening streaks</p>
            </div>
            <div class="card-body">
                @if(count($stats['binge_sessions']['top_sessions']) > 0)
                    <div class="binge-sessions-list">
                        @foreach($stats['binge_sessions']['top_sessions'] as $index => $session)
                            <div class="session-item" onclick="openSessionModal({{ $index }})" style="cursor: pointer;">
                                <div class="session-rank">{{ $index + 1 }}</div>
                                <div class="session-info">
                                    <div class="session-tracks">{{ $session['track_count'] }} tracks</div>
                                    <div class="session-duration">{{ $session['duration_minutes'] }}min music</div>
                                    <div class="session-time">{{ $session['start_time']->format('M j, g:i A') }}</div>
                                </div>
                                <div class="session-badge">🔥</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="binge-summary">
                        <span>{{ $stats['binge_sessions']['total_sessions'] }} total sessions</span>
                        <span>{{ $stats['binge_sessions']['longest_session_tracks'] }} tracks longest</span>
                    </div>
                @else
                    <p class="no-data">No binge sessions detected yet. Keep listening!</p>
                @endif
            </div>
        </div>
        
        <!-- Discovery Rate -->
        <div class="card">
            <div class="card-header">
                <h3>🎵 Discovery Rate</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">How much new music you explore daily</p>
            </div>
            <div class="card-body">
                <div class="discovery-stats">
                    <div class="discovery-stat">
                        <div class="stat-value">{{ $stats['discovery_rate']['avg_tracks_per_day'] }}</div>
                        <div class="stat-label">Tracks/Day</div>
                    </div>
                    <div class="discovery-stat">
                        <div class="stat-value">{{ $stats['discovery_rate']['avg_unique_tracks_per_day'] }}</div>
                        <div class="stat-label">Unique/Day</div>
                    </div>
                    <div class="discovery-stat">
                        <div class="stat-value">{{ $stats['discovery_rate']['discovery_percentage'] }}%</div>
                        <div class="stat-label">Discovery Rate</div>
                    </div>
                </div>
                
                @if(count($stats['discovery_rate']['recent_days']) > 0)
                    <div class="recent-days">
                        <h4>Recent Days</h4>
                        <div class="days-list">
                            @foreach($stats['discovery_rate']['recent_days'] as $date => $dayStats)
                                <div class="day-item">
                                    <div class="day-date">{{ \Carbon\Carbon::parse($date)->format('M j') }}</div>
                                    <div class="day-stats">
                                        <span class="unique">{{ $dayStats['unique'] }} new</span>
                                        <span class="repeat">{{ $dayStats['repeat'] }} repeat</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Top Lists Grid -->
    <div class="content-grid" style="margin-top: 2rem;">
        <!-- Top Artists -->
        <div class="card">
            <div class="card-header">
                <h3>Top Artists</h3>
            </div>
            <div class="card-body">
                @if(count($stats['top_artists']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_artists'] as $index => $artist)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="item-info">
                                    <div class="item-name">
                                        <a href="{{ route('artists.show', ['artist' => urlencode($artist['name'])]) }}" class="item-link">
                                            {{ $artist['name'] }}
                                        </a>
                                    </div>
                                    <div class="item-details">
                                        {{ $artist['play_count'] }} plays • 
                                        {{ $artist['unique_tracks_count'] }} songs • 
                                        {{ round($artist['total_duration_ms'] / 1000 / 60 / 60, 1) }}h
                                    </div>
                                </div>
                                <div class="play-count">{{ $artist['play_count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No artist data available yet. Start listening to music!</p>
                @endif
            </div>
        </div>
        
        <!-- Top Tracks -->
        <div class="card">
            <div class="card-header">
                <h3>Top Tracks</h3>
            </div>
            <div class="card-body">
                @if(count($stats['top_tracks']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_tracks'] as $index => $track)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="track-cover">
                                    @if($track['album_image_url'])
                                        <img src="{{ $track['album_image_url'] }}" alt="{{ $track['album_name'] }}" class="cover-image">
                                    @else
                                        <div class="cover-placeholder">
                                            <i class="fas fa-music"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <div class="item-name">
                                        <a href="{{ route('tracks.show', ['track' => $track['spotify_track_id']]) }}" class="item-link">
                                            {{ $track['track_name'] }}
                                        </a>
                                        <x-mood-pills :moods="$track['moods'] ?? []" />
                                    </div>
                                    <div class="item-details">
                                        {{ $track['artists_string'] }} • {{ $track['album_name'] }}
                                    </div>
                                </div>
                                <div class="play-count">{{ $track['play_count'] }}</div>
                                <button class="mood-trigger{{ !empty($track['moods']) ? ' has-moods' : '' }}" 
                                        data-track-id="{{ $track['spotify_track_id'] }}"
                                        onclick="openMoodPopup('{{ $track['spotify_track_id'] }}', '{{ addslashes($track['track_name']) }}', '{{ addslashes($track['artists_string']) }}')"
                                        title="{{ !empty($track['moods']) ? 'Manage moods' : 'Add mood to track' }}">
                                    <i class="fas fa-{{ !empty($track['moods']) ? 'tags' : 'plus' }}"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No track data available yet. Start listening to music!</p>
                @endif
            </div>
        </div>
        
        <!-- Top Albums -->
        <div class="card">
            <div class="card-header">
                <h3>Top Albums</h3>
            </div>
            <div class="card-body">
                @if(count($stats['top_albums']) > 0)
                    <div class="top-list">
                        @foreach($stats['top_albums'] as $index => $album)
                            <div class="top-list-item">
                                <div class="rank-number">{{ $index + 1 }}</div>
                                <div class="track-cover">
                                    @if($album['album_image_url'])
                                        <img src="{{ $album['album_image_url'] }}" alt="{{ $album['album_name'] }}" class="cover-image">
                                    @else
                                        <div class="cover-placeholder">
                                            <i class="fas fa-compact-disc"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-info">
                                    <div class="item-name">
                                        <a href="{{ route('albums.show', ['album' => urlencode($album['album_name'])]) }}" class="item-link">
                                            {{ $album['album_name'] }}
                                        </a>
                                    </div>
                                    <div class="item-details">
                                        {{ $album['artists_string'] }} • 
                                        {{ $album['unique_tracks_count'] }} tracks
                                    </div>
                                </div>
                                <div class="play-count">{{ $album['play_count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-data">No album data available yet. Start listening to music!</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div id="sessionModal" class="session-modal">
        <div class="session-modal-overlay" onclick="closeSessionModal()"></div>
        <div class="session-modal-content">
            <div class="session-modal-header">
                <h3 id="sessionModalTitle">Session Details</h3>
                <button class="session-modal-close" onclick="closeSessionModal()">&times;</button>
            </div>
            <div class="session-modal-body">
                <div id="sessionTracksContainer"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const sessionData = @json($stats['binge_sessions']['top_sessions']);

    function openSessionModal(sessionIndex) {
        const session = sessionData[sessionIndex];
        const modal = document.getElementById('sessionModal');
        const title = document.getElementById('sessionModalTitle');
        const container = document.getElementById('sessionTracksContainer');

        title.textContent = `Session ${sessionIndex + 1} - ${session.track_count} tracks`;

        let tracksHtml = '<div class="session-tracks-list">';
        session.tracks.forEach((track, index) => {
            const playedAt = new Date(track.played_at);
            const timeStr = playedAt.toLocaleTimeString('nl-NL', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: 'Europe/Amsterdam'
            });

            const trackUrl = `/tracks/${track.spotify_track_id}`;

            tracksHtml += `
                <a href="${trackUrl}" class="session-track-item">
                    <div class="session-track-number">${index + 1}</div>
                    ${track.album_image_url ?
                        `<img src="${track.album_image_url}" alt="${track.name}" class="session-track-image">` :
                        '<div class="session-track-placeholder"><i class="fas fa-music"></i></div>'
                    }
                    <div class="session-track-info">
                        <div class="session-track-name">${track.name}</div>
                        <div class="session-track-artist">${track.artists}</div>
                    </div>
                    <div class="session-track-time">${timeStr}</div>
                </a>
            `;
        });
        tracksHtml += '</div>';

        container.innerHTML = tracksHtml;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSessionModal() {
        const modal = document.getElementById('sessionModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSessionModal();
        }
    });
</script>
@endsection