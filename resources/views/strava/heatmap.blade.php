@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Route Heatmap</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('strava.index') }}">Strava</a></li>
                    <li>Heatmap</li>
                </ul>
            </div>
            <a href="{{ route('strava.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div id="map" style="height: calc(100vh - 220px); min-height: 500px; border-radius: 0 0 0.5rem 0.5rem;"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
    function decodePolyline(encoded) {
        const points = [];
        let index = 0, lat = 0, lng = 0;
        while (index < encoded.length) {
            let b, shift = 0, result = 0;
            do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
            lat += (result & 1) ? ~(result >> 1) : (result >> 1);
            shift = 0; result = 0;
            do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
            lng += (result & 1) ? ~(result >> 1) : (result >> 1);
            points.push([lat / 1e5, lng / 1e5]);
        }
        return points;
    }

    const map = L.map('map');

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO',
        maxZoom: 19,
    }).addTo(map);

    const polylines = @json($polylines);
    const heatPoints = [];

    polylines.forEach(encoded => {
        if (!encoded) return;
        decodePolyline(encoded).forEach(([lat, lng]) => heatPoints.push([lat, lng, 1]));
    });

    if (heatPoints.length > 0) {
        L.heatLayer(heatPoints, {
            radius: 8,
            blur: 12,
            maxZoom: 17,
            gradient: { 0.3: '#fc4c02', 0.6: '#ff8c00', 1.0: '#ffffff' },
        }).addTo(map);

        // Fit map to all points
        const lats = heatPoints.map(p => p[0]);
        const lngs = heatPoints.map(p => p[1]);
        map.fitBounds([
            [Math.min(...lats), Math.min(...lngs)],
            [Math.max(...lats), Math.max(...lngs)],
        ], { padding: [30, 30] });
    } else {
        map.setView([52.3676, 4.9041], 12);
    }
</script>
@endpush
