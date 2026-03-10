@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Uitgaven Statistieken</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('expenses.index') }}">Uitgaven</a></li>
                    <li>Statistieken</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <form method="GET" action="{{ route('expenses.stats') }}" style="display: flex; gap: 0.5rem; align-items: center;">
                    <select name="year" onchange="this.form.submit()" class="form-control" style="width: auto;">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Terug
                </a>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon expense-accent">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['year_total_formatted'] }}</div>
                <div class="stat-label">Totaal {{ $year }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon expense-accent">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($stats['overview']['year_count']) }}</div>
                <div class="stat-label">Aantal uitgaven</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['avg_per_month_formatted'] }}</div>
                <div class="stat-label">Gem. per maand</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['overview']['avg_per_expense_formatted'] }}</div>
                <div class="stat-label">Gem. per uitgave</div>
            </div>
        </div>
    </div>

    <!-- Comparisons Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Weekly Comparison -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-week" style="margin-right: 8px;"></i> Wekelijkse vergelijking</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Deze week</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #ef4444;">
                            {{ $stats['weekly_comparison']['this_week_formatted'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $stats['weekly_comparison']['this_week_count'] }} uitgaven</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Vorige week</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #6b7280;">
                            {{ $stats['weekly_comparison']['last_week_formatted'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $stats['weekly_comparison']['last_week_count'] }} uitgaven</div>
                    </div>
                </div>
                @if($stats['weekly_comparison']['percent_change'] != 0)
                    <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <span style="color: {{ $stats['weekly_comparison']['trend'] === 'down' ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            <i class="fas fa-arrow-{{ $stats['weekly_comparison']['trend'] }}"></i>
                            {{ abs($stats['weekly_comparison']['percent_change']) }}%
                        </span>
                        <span style="color: #6b7280; font-size: 0.875rem;">vs vorige week</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i> Maandelijkse vergelijking</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">{{ $stats['monthly_comparison']['this_month_name'] }}</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #ef4444;">
                            {{ $stats['monthly_comparison']['this_month_formatted'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $stats['monthly_comparison']['this_month_count'] }} uitgaven</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">{{ $stats['monthly_comparison']['last_month_name'] }}</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #6b7280;">
                            {{ $stats['monthly_comparison']['last_month_formatted'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $stats['monthly_comparison']['last_month_count'] }} uitgaven</div>
                    </div>
                </div>
                @if($stats['monthly_comparison']['percent_change'] != 0)
                    <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <span style="color: {{ $stats['monthly_comparison']['trend'] === 'down' ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            <i class="fas fa-arrow-{{ $stats['monthly_comparison']['trend'] }}"></i>
                            {{ abs($stats['monthly_comparison']['percent_change']) }}%
                        </span>
                        <span style="color: #6b7280; font-size: 0.875rem;">vs vorige maand</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Category Distribution -->
    @if(count($stats['category_totals']) > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie" style="margin-right: 8px;"></i> Verdeling per categorie ({{ $year }})</h3>
            </div>
            <div class="card-body">
                <div class="category-distribution">
                    @foreach($stats['category_totals'] as $cat)
                        @if($cat['amount'] > 0)
                            <div class="category-bar-item">
                                <div class="category-bar-header">
                                    <span class="category-bar-label">
                                        <i class="fas {{ $cat['icon'] }}" style="color: {{ $cat['color'] }}; margin-right: 0.5rem;"></i>
                                        {{ $cat['name'] }}
                                    </span>
                                    <span class="category-bar-value">
                                        {{ $cat['formatted'] }}
                                        <span style="color: #6b7280; font-size: 0.75rem;">({{ $cat['percentage'] }}%)</span>
                                    </span>
                                </div>
                                <div class="category-bar-track">
                                    <div class="category-bar-fill" style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }};"></div>
                                </div>
                                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">{{ $cat['count'] }} uitgaven</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Monthly Breakdown by Category -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3><i class="fas fa-table" style="margin-right: 8px;"></i> Maandoverzicht per categorie ({{ $year }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table expense-matrix-table">
                    <thead>
                        <tr>
                            <th>Categorie</th>
                            @foreach($stats['monthly_by_category'] as $month)
                                <th style="text-align: right;">{{ $month['month_name'] }}</th>
                            @endforeach
                            <th style="text-align: right; background: var(--table-hover-bg);">Totaal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            @php
                                $catTotal = 0;
                            @endphp
                            <tr>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas {{ $category->icon }}" style="color: {{ $category->color }};"></i>
                                        {{ $category->name }}
                                    </span>
                                </td>
                                @foreach($stats['monthly_by_category'] as $month)
                                    @php
                                        $amount = $month['categories'][$category->id]['amount'] ?? 0;
                                        $catTotal += $amount;
                                    @endphp
                                    <td style="text-align: right; {{ $amount > 0 ? 'color: #ef4444;' : 'color: #9ca3af;' }}">
                                        {{ $amount > 0 ? '€'.number_format($amount, 0, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                                <td style="text-align: right; font-weight: 600; background: var(--table-hover-bg); color: #ef4444;">
                                    €{{ number_format($catTotal, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: 600; background: var(--table-hover-bg);">
                            <td>Totaal</td>
                            @foreach($stats['monthly_totals'] as $month)
                                <td style="text-align: right; color: #ef4444;">
                                    {{ $month['amount'] > 0 ? '€'.number_format($month['amount'], 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                            <td style="text-align: right; color: #ef4444; font-size: 1.1rem;">
                                {{ $stats['overview']['year_total_formatted'] }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    @if(count($stats['monthly_totals']) > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar" style="margin-right: 8px;"></i> Uitgaven per maand ({{ $year }})</h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    @endif

    <!-- Top Expenses & Yearly History -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Top Expenses -->
        @if(count($stats['top_expenses']) > 0)
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy" style="margin-right: 8px; color: #f59e0b;"></i> Grootste uitgaven ({{ $year }})</h3>
                </div>
                <div class="card-body">
                    <div class="top-expenses-list">
                        @foreach($stats['top_expenses'] as $index => $expense)
                            <div class="top-expense-item">
                                <span class="top-expense-rank">{{ $index + 1 }}</span>
                                <div class="top-expense-content">
                                    <div class="top-expense-amount">{{ $expense['formatted'] }}</div>
                                    <div class="top-expense-details">
                                        {{ $expense['description'] ?: 'Geen beschrijving' }}
                                        <span class="expense-category-badge-small" style="background-color: {{ $expense['category']['color'] }}20; color: {{ $expense['category']['color'] }}; border: 1px solid {{ $expense['category']['color'] }}40;">
                                            {{ $expense['category']['name'] }}
                                        </span>
                                    </div>
                                    <div class="top-expense-date">{{ $expense['date_formatted'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Yearly History -->
        @if(count($stats['yearly_history']) > 0)
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-history" style="margin-right: 8px;"></i> Jaaroverzicht</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jaar</th>
                                    <th style="text-align: right;">Totaal</th>
                                    <th style="text-align: right;">Gem/maand</th>
                                    <th style="text-align: right;">Aantal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['yearly_history'] as $yearData)
                                    <tr class="{{ $yearData['year'] == $year ? 'table-active' : '' }}">
                                        <td>
                                            <a href="{{ route('expenses.stats', ['year' => $yearData['year']]) }}" style="font-weight: 600;">
                                                {{ $yearData['year'] }}
                                            </a>
                                        </td>
                                        <td style="text-align: right; color: #ef4444; font-weight: 600;">{{ $yearData['formatted'] }}</td>
                                        <td style="text-align: right;">{{ $yearData['avg_per_month'] }}</td>
                                        <td style="text-align: right;">{{ $yearData['count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Trends Chart -->
    @if(count($stats['trends']) > 0)
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right: 8px;"></i> Dagelijkse uitgaven ({{ $year }})</h3>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" height="80"></canvas>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart');
if (monthlyCtx) {
    const monthlyData = @json($stats['monthly_totals']);
    const categoryData = @json($stats['monthly_by_category']);
    const categories = @json($categories);

    // Create datasets for each category
    const datasets = categories.map(cat => ({
        label: cat.name,
        data: categoryData.map(m => m.categories[cat.id]?.amount || 0),
        backgroundColor: cat.color + '80',
        borderColor: cat.color,
        borderWidth: 1,
    }));

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(m => m.month_name),
            datasets: datasets
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    ticks: {
                        callback: value => '€' + value.toLocaleString('nl-NL')
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': €' + ctx.raw.toLocaleString('nl-NL', {minimumFractionDigits: 2})
                    }
                }
            }
        }
    });
}

// Trends Chart
const trendsCtx = document.getElementById('trendsChart');
if (trendsCtx) {
    const trendsData = @json($stats['trends']);

    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendsData.map(d => d.date_formatted),
            datasets: [{
                label: 'Dagelijks totaal',
                data: trendsData.map(d => d.total),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    ticks: {
                        callback: value => '€' + value.toLocaleString('nl-NL')
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: ctx => '€' + ctx.raw.toLocaleString('nl-NL', {minimumFractionDigits: 2})
                    }
                }
            }
        }
    });
}
</script>

<style>
.expense-accent {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.category-distribution {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.category-bar-item {
    padding: 0.75rem;
    background: var(--card-bg);
    border-radius: 0.5rem;
}

.category-bar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.category-bar-label {
    font-weight: 500;
}

.category-bar-value {
    font-weight: 600;
    color: #ef4444;
}

.category-bar-track {
    height: 8px;
    background: var(--border-color);
    border-radius: 4px;
    overflow: hidden;
}

.category-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.expense-matrix-table {
    font-size: 0.875rem;
}

.expense-matrix-table th,
.expense-matrix-table td {
    padding: 0.5rem;
    white-space: nowrap;
}

.top-expenses-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.top-expense-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 0.75rem;
    background: var(--card-bg);
    border-radius: 0.5rem;
}

.top-expense-rank {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}

.top-expense-content {
    flex: 1;
    min-width: 0;
}

.top-expense-amount {
    font-size: 1.125rem;
    font-weight: 600;
    color: #ef4444;
}

.top-expense-details {
    font-size: 0.875rem;
    color: var(--text-primary);
    margin-top: 0.25rem;
}

.top-expense-date {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.expense-category-badge-small {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 500;
    margin-left: 0.5rem;
}

.table-active {
    background: var(--table-hover-bg);
}

@media (max-width: 768px) {
    .expense-matrix-table {
        font-size: 0.75rem;
    }

    .expense-matrix-table th,
    .expense-matrix-table td {
        padding: 0.25rem;
    }
}
</style>
@endsection
