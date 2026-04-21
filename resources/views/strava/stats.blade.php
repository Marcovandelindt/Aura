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

    <!-- Streaks -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header"><h3><i class="fas fa-fire"></i> Streaks</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; text-align: center;">
                <div>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #fc4c02;">{{ $streaks['current'] }}</div>
                    <div style="color: var(--text-muted); font-size: 0.875rem;">Current Streak (days)</div>
                </div>
                <div>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #fc4c02;">{{ $streaks['longest'] }}</div>
                    <div style="color: var(--text-muted); font-size: 0.875rem;">Longest Streak (days)</div>
                </div>
                <div>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #fc4c02;">{{ $streaks['total_active_days'] }}</div>
                    <div style="color: var(--text-muted); font-size: 0.875rem;">Total Active Days</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Calendar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header"><h3><i class="fas fa-calendar-alt"></i> Activity Calendar (last 12 months)</h3></div>
        <div class="card-body" style="overflow-x: auto;">
            @php
                $today = \Carbon\Carbon::today();
                $startDate = $today->copy()->subYear()->startOfWeek(\Carbon\Carbon::MONDAY);
                $weeks = [];
                $current = $startDate->copy();
                while ($current->lte($today)) {
                    $week = [];
                    for ($d = 0; $d < 7; $d++) {
                        $week[] = $current->copy();
                        $current->addDay();
                    }
                    $weeks[] = $week;
                }
                $monthLabels = [];
                foreach ($weeks as $wi => $week) {
                    $month = $week[0]->format('M');
                    $prev = $wi > 0 ? $weeks[$wi - 1][0]->format('M') : null;
                    $monthLabels[$wi] = $month !== $prev ? $month : '';
                }
            @endphp
            <div style="display: flex; gap: 3px;">
                <!-- Day labels -->
                <div style="display: flex; flex-direction: column; gap: 3px; margin-top: 20px; margin-right: 4px;">
                    @foreach(['M','T','W','T','F','S','S'] as $label)
                        <div style="width: 12px; height: 12px; font-size: 9px; color: var(--text-muted); line-height: 12px; text-align: center;">{{ $label }}</div>
                    @endforeach
                </div>
                <!-- Weeks -->
                <div>
                    <!-- Month labels -->
                    <div style="display: flex; gap: 3px; margin-bottom: 4px; height: 16px;">
                        @foreach($weeks as $wi => $week)
                            <div style="width: 12px; font-size: 9px; color: var(--text-muted); white-space: nowrap; overflow: visible;">{{ $monthLabels[$wi] }}</div>
                        @endforeach
                    </div>
                    <!-- Grid -->
                    <div style="display: flex; gap: 3px;">
                        @foreach($weeks as $week)
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                @foreach($week as $day)
                                    @php
                                        $key = $day->format('Y-m-d');
                                        $data = $calendarData[$key] ?? null;
                                        $isFuture = $day->isAfter($today);
                                        if ($isFuture) {
                                            $bg = 'transparent';
                                        } elseif (!$data) {
                                            $bg = 'var(--border-color)';
                                        } elseif ($data['km'] < 3) {
                                            $bg = '#f97316';
                                        } elseif ($data['km'] < 7) {
                                            $bg = '#fc4c02';
                                        } else {
                                            $bg = '#c73a00';
                                        }
                                        $tooltip = $data
                                            ? $day->format('d M Y').': '.$data['count'].' '.Str::plural('activity', $data['count']).' ('.$data['km'].' km)'
                                            : $day->format('d M Y').': no activity';
                                    @endphp
                                    <div data-tooltip="{{ $isFuture ? '' : $tooltip }}"
                                         style="width: 12px; height: 12px; border-radius: 2px; background: {{ $bg }}; cursor: {{ $isFuture ? 'default' : 'pointer' }};"></div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- Legend -->
            <div style="display: flex; align-items: center; gap: 6px; margin-top: 12px; font-size: 0.75rem; color: var(--text-muted);">
                <span>Less</span>
                <div style="width: 12px; height: 12px; border-radius: 2px; background: var(--border-color);"></div>
                <div style="width: 12px; height: 12px; border-radius: 2px; background: #f97316;"></div>
                <div style="width: 12px; height: 12px; border-radius: 2px; background: #fc4c02;"></div>
                <div style="width: 12px; height: 12px; border-radius: 2px; background: #c73a00;"></div>
                <span>More</span>
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

    <!-- Year Comparison -->
    @if(count($yearComparison) > 1)
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header"><h3><i class="fas fa-calendar"></i> Year Comparison</h3></div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Activities</th>
                        <th>Distance</th>
                        <th>Moving Time</th>
                        <th>Elevation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearComparison as $year)
                        @php
                            $h = intdiv($year['total_time'], 3600);
                            $m = intdiv($year['total_time'] % 3600, 60);
                        @endphp
                        <tr>
                            <td><strong>{{ $year['year'] }}</strong></td>
                            <td>{{ $year['count'] }}</td>
                            <td>{{ number_format($year['total_km'], 1) }} km</td>
                            <td>{{ $h }}h {{ $m }}m</td>
                            <td>{{ number_format($year['total_elevation']) }} m</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Charts row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-bar"></i> Distance per Month (last 12 months)</h3></div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-bar"></i> Distance per Week (last 12 weeks)</h3></div>
            <div class="card-body">
                <canvas id="weeklyChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-calendar-week"></i> Most Active Days</h3></div>
            <div class="card-body">
                <canvas id="weekdayChart" height="80"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Activity Types</h3></div>
            <div class="card-body" style="display: flex; align-items: center; justify-content: center;">
                <canvas id="typeChart" height="180"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Calendar tooltip
    const tooltip = document.createElement('div');
    tooltip.style.cssText = `
        position: fixed; z-index: 9999; pointer-events: none; opacity: 0;
        background: rgba(0,0,0,0.85); color: #fff; font-size: 12px;
        padding: 6px 10px; border-radius: 6px; white-space: nowrap;
        transition: opacity 0.1s; box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    `;
    document.body.appendChild(tooltip);

    document.querySelectorAll('[data-tooltip]').forEach(el => {
        const text = el.getAttribute('data-tooltip');
        if (!text) return;

        el.addEventListener('mouseenter', e => {
            tooltip.textContent = text;
            tooltip.style.opacity = '1';
        });
        el.addEventListener('mousemove', e => {
            tooltip.style.left = (e.clientX + 12) + 'px';
            tooltip.style.top = (e.clientY - 28) + 'px';
        });
        el.addEventListener('mouseleave', () => {
            tooltip.style.opacity = '0';
        });
    });

    const stravaOrange = '#fc4c02';

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyData['labels']),
            datasets: [{ label: 'km', data: @json($monthlyData['km']), backgroundColor: stravaOrange, borderRadius: 4 }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' km' } } }
        }
    });

    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: @json($weeklyDistanceData['labels']),
            datasets: [{ label: 'km', data: @json($weeklyDistanceData['km']), backgroundColor: stravaOrange, borderRadius: 4 }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' km' } } }
        }
    });

    const typeColors = ['#fc4c02','#e63e00','#ff7a45','#ffa07a','#ffcba4','#ffe4cc'];
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: @json($typeDistribution['labels']),
            datasets: [{ data: @json($typeDistribution['counts']), backgroundColor: typeColors, borderWidth: 2 }]
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

    new Chart(document.getElementById('weekdayChart'), {
        type: 'bar',
        data: {
            labels: @json($weekdayData['labels']),
            datasets: [{ label: 'Activities', data: @json($weekdayData['counts']), backgroundColor: stravaOrange, borderRadius: 4 }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
@endpush
