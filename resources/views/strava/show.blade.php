@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>{{ $activity->name }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('strava.index') }}">Strava</a></li>
                    <li>{{ $activity->name }}</li>
                </ul>
            </div>
            <div>
                <a href="https://www.strava.com/activities/{{ $activity->strava_id }}" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-external-link-alt"></i>
                    Open in Strava
                </a>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-{{ match(true) {
                        str_contains(strtolower($activity->sport_type), 'walk') => 'walking',
                        str_contains(strtolower($activity->sport_type), 'run') => 'running',
                        str_contains(strtolower($activity->sport_type), 'ride') => 'bicycle',
                        default => 'dumbbell'
                    } }}"></i>
                    {{ $activity->sport_type }} &mdash; {{ $activity->start_date_local->format('d M Y, H:i') }}
                </h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Distance</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ $activity->distance_in_km }} km</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Moving Time</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ $activity->moving_time_pretty }}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Elevation Gain</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ round($activity->total_elevation_gain) }} m</div>
                    </div>
                    @if($activity->average_speed)
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Avg Speed</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ round($activity->average_speed * 3.6, 1) }} km/h</div>
                    </div>
                    @endif
                    @if($activity->average_heartrate)
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Avg Heart Rate</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ round($activity->average_heartrate) }} bpm</div>
                    </div>
                    @endif
                    @if($activity->max_heartrate)
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Max Heart Rate</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ $activity->max_heartrate }} bpm</div>
                    </div>
                    @endif
                    @if($activity->calories)
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Calories</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">{{ round($activity->calories) }} kcal</div>
                    </div>
                    @endif
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Elapsed Time</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">
                            @php
                                $h = intdiv($activity->elapsed_time, 3600);
                                $m = intdiv($activity->elapsed_time % 3600, 60);
                                $s = $activity->elapsed_time % 60;
                            @endphp
                            {{ $h > 0 ? "{$h}:{$m}:{$s}" : "{$m}:{$s}" }}
                        </div>
                    </div>
                </div>

                @if($activity->description)
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">Description</div>
                        <p>{{ $activity->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Map -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-map-marked-alt"></i> Route</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($activity->map_polyline)
                    <div id="map" style="height: 400px; border-radius: 0 0 0.5rem 0.5rem;"></div>
                @else
                    <div style="height: 400px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                        <div style="text-align: center;">
                            <i class="fas fa-map" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            No route data available
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if($activity->map_polyline)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Decode Google encoded polyline
    function decodePolyline(encoded) {
        const points = [];
        let index = 0, lat = 0, lng = 0;

        while (index < encoded.length) {
            let b, shift = 0, result = 0;
            do {
                b = encoded.charCodeAt(index++) - 63;
                result |= (b & 0x1f) << shift;
                shift += 5;
            } while (b >= 0x20);
            lat += (result & 1) ? ~(result >> 1) : (result >> 1);

            shift = 0; result = 0;
            do {
                b = encoded.charCodeAt(index++) - 63;
                result |= (b & 0x1f) << shift;
                shift += 5;
            } while (b >= 0x20);
            lng += (result & 1) ? ~(result >> 1) : (result >> 1);

            points.push([lat / 1e5, lng / 1e5]);
        }

        return points;
    }

    const polyline = @json($activity->map_polyline);
    const points = decodePolyline(polyline);

    const map = L.map('map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const route = L.polyline(points, { color: '#fc4c02', weight: 4, opacity: 0.85 }).addTo(map);

    // Start & end markers
    if (points.length > 0) {
        L.circleMarker(points[0], { radius: 8, color: '#22c55e', fillColor: '#22c55e', fillOpacity: 1 })
            .bindTooltip('Start').addTo(map);
        L.circleMarker(points[points.length - 1], { radius: 8, color: '#ef4444', fillColor: '#ef4444', fillOpacity: 1 })
            .bindTooltip('Finish').addTo(map);
    }

    map.fitBounds(route.getBounds(), { padding: [20, 20] });
</script>
@endpush
@endif
