@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Weekly Highlights</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Weekly</li>
                </ul>
            </div>
            <!-- Week navigation -->
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="{{ route('weekly.index', ['week' => $weekOffset + 1]) }}" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <span style="font-weight: 600; min-width: 200px; text-align: center;">
                    {{ $weekStart->format('d M') }} — {{ $weekEnd->format('d M Y') }}
                </span>
                @if($weekOffset > 0)
                    <a href="{{ route('weekly.index', ['week' => $weekOffset - 1]) }}" class="btn btn-secondary">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="btn btn-secondary" style="opacity: 0.4; cursor: default;"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

        <!-- Strava -->
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #fc4c02;"><i class="fas fa-running"></i></span>
                <h3>Activities</h3>
            </div>
            <div class="card-body">
                @if($data['strava']['count'] === 0)
                    <p style="color: var(--text-muted);">No activities this week.</p>
                @else
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $data['strava']['count'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Activities</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $data['strava']['total_km'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">km</div>
                        </div>
                        <div>
                            @php $h = intdiv($data['strava']['total_time'], 3600); $m = intdiv($data['strava']['total_time'] % 3600, 60); @endphp
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $h }}h {{ $m }}m</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Moving time</div>
                        </div>
                    </div>
                    @if($data['strava']['best'])
                        <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.875rem;">
                            <span style="color: var(--text-muted);">Best activity:</span>
                            <a href="{{ route('strava.show', $data['strava']['best']) }}" style="margin-left: 0.5rem;">
                                <strong>{{ $data['strava']['best']->name }}</strong>
                            </a>
                            — {{ $data['strava']['best']->distance_in_km }} km
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Music -->
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #1DB954;"><i class="fab fa-spotify"></i></span>
                <h3>Music</h3>
            </div>
            <div class="card-body">
                @if($data['music']['count'] === 0)
                    <p style="color: var(--text-muted);">No tracks this week.</p>
                @else
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ number_format($data['music']['count']) }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Tracks played</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $data['music']['unique_tracks'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Unique tracks</div>
                        </div>
                    </div>
                    @if($data['music']['top_track'])
                        <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.4rem;">
                            <div>
                                <span style="color: var(--text-muted);">Most played:</span>
                                <a href="{{ route('tracks.show', $data['music']['top_track']['track']->spotify_track_id) }}" style="margin-left: 0.5rem;">
                                    <strong>{{ $data['music']['top_track']['track']->track_name }}</strong>
                                </a>
                                <span style="color: var(--text-muted);"> × {{ $data['music']['top_track']['count'] }}</span>
                            </div>
                            @if($data['music']['top_artist'])
                                <div>
                                    <span style="color: var(--text-muted);">Top artist:</span>
                                    <strong style="margin-left: 0.5rem;">{{ $data['music']['top_artist'] }}</strong>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Watching -->
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #e50914;"><i class="fas fa-film"></i></span>
                <h3>Movies & Series</h3>
            </div>
            <div class="card-body">
                @if($data['watching']['movies_count'] === 0 && $data['watching']['episodes_count'] === 0)
                    <p style="color: var(--text-muted);">Nothing watched this week.</p>
                @else
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $data['watching']['movies_count'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Movies</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $data['watching']['episodes_count'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Episodes</div>
                        </div>
                    </div>
                    @if($data['watching']['movies']->isNotEmpty() || $data['watching']['series']->isNotEmpty())
                        <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.3rem;">
                            @foreach($data['watching']['movies'] as $watch)
                                <div><i class="fas fa-film" style="color: var(--text-muted); width: 16px;"></i> {{ $watch->movie->title }}</div>
                            @endforeach
                            @foreach($data['watching']['series'] as $seriesName => $episodes)
                                <div><i class="fas fa-tv" style="color: var(--text-muted); width: 16px;"></i> {{ $seriesName }} <span style="color: var(--text-muted);">× {{ $episodes->count() }} ep</span></div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Gaming -->
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #006FCD;"><i class="fas fa-gamepad"></i></span>
                <h3>Gaming</h3>
            </div>
            <div class="card-body">
                @if($data['gaming']['total_minutes'] === 0)
                    <p style="color: var(--text-muted);">No gaming this week.</p>
                @else
                    @php
                        $th = intdiv($data['gaming']['total_minutes'], 60);
                        $tm = $data['gaming']['total_minutes'] % 60;
                    @endphp
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $th }}h {{ $tm }}m</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Total</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ intdiv($data['gaming']['ps_minutes'], 60) }}h</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">PlayStation</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ intdiv($data['gaming']['ns_minutes'], 60) }}h</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Switch</div>
                        </div>
                    </div>
                    <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.3rem;">
                        @if($data['gaming']['top_ps'])
                            <div><i class="fas fa-gamepad" style="color: var(--text-muted); width: 16px;"></i>
                                <strong>{{ $data['gaming']['top_ps']['game']->name }}</strong>
                                <span style="color: var(--text-muted);"> — {{ intdiv($data['gaming']['top_ps']['minutes'], 60) }}h {{ $data['gaming']['top_ps']['minutes'] % 60 }}m</span>
                            </div>
                        @endif
                        @if($data['gaming']['top_ns'])
                            <div><i class="fas fa-gamepad" style="color: var(--text-muted); width: 16px;"></i>
                                <strong>{{ $data['gaming']['top_ns']['game']->name }}</strong>
                                <span style="color: var(--text-muted);"> — {{ intdiv($data['gaming']['top_ns']['minutes'], 60) }}h {{ $data['gaming']['top_ns']['minutes'] % 60 }}m</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Health -->
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #10b981;"><i class="fas fa-heartbeat"></i></span>
                <h3>Health</h3>
            </div>
            <div class="card-body">
                @if(!$data['health']['total_steps'])
                    <p style="color: var(--text-muted);">No health data this week.</p>
                @else
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ number_format($data['health']['total_steps']) }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Total steps</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ number_format(round($data['health']['avg_steps'])) }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Avg steps/day</div>
                        </div>
                    </div>
                    @if($data['health']['best_day'])
                        <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.875rem;">
                            <span style="color: var(--text-muted);">Best day:</span>
                            <strong style="margin-left: 0.5rem;">{{ $data['health']['best_day']->date->format('l') }}</strong>
                            — {{ number_format($data['health']['best_day']->steps) }} steps
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Expenses -->
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #f59e0b;"><i class="fas fa-wallet"></i></span>
                <h3>Uitgaven</h3>
            </div>
            <div class="card-body">
                @if($data['expenses']['count'] === 0)
                    <p style="color: var(--text-muted);">Geen uitgaven deze week.</p>
                @else
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">€{{ number_format($data['expenses']['total'], 2, ',', '.') }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Totaal</div>
                        </div>
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700;">{{ $data['expenses']['count'] }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">Transacties</div>
                        </div>
                    </div>
                    <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.3rem;">
                        @foreach($data['expenses']['by_category']->take(3) as $item)
                            <div style="display: flex; justify-content: space-between;">
                                <span>
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $item['category']->color }}; margin-right: 6px;"></span>
                                    {{ $item['category']->name }}
                                </span>
                                <span style="color: var(--text-muted);">€{{ number_format($item['total'], 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
