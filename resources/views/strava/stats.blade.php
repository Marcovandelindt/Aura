@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Strava Statistics</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('strava.index') }}">Strava</a></li>
                    <li>Statistics</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('strava.heatmap') }}" class="btn btn-secondary">
                    <i class="fas fa-map"></i> Heatmap
                </a>
                <a href="{{ route('strava.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Personal Records -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header"><h3><i class="fas fa-trophy"></i> Personal Records</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">

                @if($records['longest_distance'])
                    @php $a = $records['longest_distance']; @endphp
                    <a href="{{ route('strava.show', $a) }}" style="text-decoration: none; color: inherit;">
                        <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;"><i class="fas fa-route"></i> Longest Distance</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">{{ $a->distance_in_km }} km</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">{{ $a->name }} &mdash; {{ $a->start_date_local->format('d M Y') }}</div>
                        </div>
                    </a>
                @endif

                @if($records['longest_duration'])
                    @php $a = $records['longest_duration']; @endphp
                    <a href="{{ route('strava.show', $a) }}" style="text-decoration: none; color: inherit;">
                        <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;"><i class="fas fa-clock"></i> Longest Duration</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">{{ $a->moving_time_pretty }}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">{{ $a->name }} &mdash; {{ $a->start_date_local->format('d M Y') }}</div>
                        </div>
                    </a>
                @endif

                @if($records['most_elevation'])
                    @php $a = $records['most_elevation']; @endphp
                    <a href="{{ route('strava.show', $a) }}" style="text-decoration: none; color: inherit;">
                        <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;"><i class="fas fa-mountain"></i> Most Elevation</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">{{ round($a->total_elevation_gain) }} m</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">{{ $a->name }} &mdash; {{ $a->start_date_local->format('d M Y') }}</div>
                        </div>
                    </a>
                @endif

                @if($records['fastest_pace'])
                    @php $a = $records['fastest_pace']; @endphp
                    <a href="{{ route('strava.show', $a) }}" style="text-decoration: none; color: inherit;">
                        <div style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;"><i class="fas fa-tachometer-alt"></i> Fastest Avg Speed</div>
                            <div style="font-size: 1.5rem; font-weight: 700;">{{ round($a->average_speed * 3.6, 1) }} km/h</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">{{ $a->name }} &mdash; {{ $a->start_date_local->format('d M Y') }}</div>
                        </div>
                    </a>
                @endif

            </div>
        </div>
    </div>

    <!-- Charts row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">

        <!-- Weekly Distance -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-bar"></i> Distance per Week (last 12 weeks)</h3></div>
            <div class="card-body">
                <canvas id="weeklyChart" height="80"></canvas>
            </div>
        </div>

        <!-- Activity Type Distribution -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Activity Types</h3></div>
            <div class="card-body" style="display: flex; align-items: center; justify-content: center;">
                <canvas id="typeChart" height="180"></canvas>
            </div>
        </div>

    </div>

    <!-- Weekday Distribution -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-calendar-week"></i> Most Active Days</h3></div>
        <div class="card-body">
            <canvas id="weekdayChart" height="60"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const stravaOrange = '#fc4c02';
    const stravaOrangeAlpha = 'rgba(252, 76, 2, 0.15)';

    // Weekly distance chart
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: @json($weeklyDistanceData['labels']),
            datasets: [{
                label: 'km',
                data: @json($weeklyDistanceData['km']),
                backgroundColor: stravaOrange,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v + ' km' } }
            }
        }
    });

    // Type distribution chart
    const typeColors = ['#fc4c02','#e63e00','#ff7a45','#ffa07a','#ffcba4','#ffe4cc'];
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: @json($typeDistribution['labels']),
            datasets: [{
                data: @json($typeDistribution['counts']),
                backgroundColor: typeColors,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const km = @json($typeDistribution['km']);
                            return ` ${ctx.label}: ${ctx.raw} activities (${km[ctx.dataIndex]} km)`;
                        }
                    }
                }
            }
        }
    });

    // Weekday chart
    new Chart(document.getElementById('weekdayChart'), {
        type: 'bar',
        data: {
            labels: @json($weekdayData['labels']),
            datasets: [{
                label: 'Activities',
                data: @json($weekdayData['counts']),
                backgroundColor: stravaOrange,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
@endpush
