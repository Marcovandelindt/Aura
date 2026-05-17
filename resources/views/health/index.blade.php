@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Health</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Health</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('health.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i>
                    Statistics
                </a>
                <button onclick="openExportModal()" class="btn btn-secondary">
                    <i class="fas fa-download"></i>
                    Export
                </button>
                <button onclick="openAddEntryModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Entry
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon health-accent">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($stats['total_entries']) }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon health-accent">
                <i class="fas fa-shoe-prints"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['avg_steps'] ? number_format($stats['avg_steps']) : '-' }}</div>
                <div class="stat-label">Avg Steps/Day</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['avg_heart_rate'] ?? '-' }}</div>
                <div class="stat-label">Avg Heart Rate</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['avg_resting_hr'] ?? '-' }}</div>
                <div class="stat-label">Avg Resting HR</div>
            </div>
        </div>
    </div>

    @if($entries->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-heartbeat" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">No health entries yet</h3>
                <p style="color: #6b7280;">Start tracking your health by adding your first entry.</p>
                <button onclick="openAddEntryModal()" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Add Your First Entry
                </button>
            </div>
        </div>
    @else
        <!-- Calendar and Entries Side by Side -->
        <div class="health-layout">
            <!-- Calendar -->
            <div class="card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <a href="{{ route('health.index', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h3 style="margin: 0; font-size: 1rem;">{{ $currentMonth->format('F Y') }}</h3>
                        <a href="{{ route('health.index', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <div class="health-calendar health-calendar-compact">
                        <div class="calendar-header">M</div>
                        <div class="calendar-header">T</div>
                        <div class="calendar-header">W</div>
                        <div class="calendar-header">T</div>
                        <div class="calendar-header">F</div>
                        <div class="calendar-header">S</div>
                        <div class="calendar-header">S</div>

                        @php
                            $startOfMonth = $currentMonth->copy()->startOfMonth();
                            $endOfMonth = $currentMonth->copy()->endOfMonth();
                            $startDay = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                            $endDay = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                            $today = now()->format('Y-m-d');
                        @endphp

                        @while($startDay <= $endDay)
                            @php
                                $dateKey = $startDay->format('Y-m-d');
                                $entry = $monthlyEntries[$dateKey] ?? null;
                                $isCurrentMonth = $startDay->month === $currentMonth->month;
                                $isToday = $dateKey === $today;
                            @endphp
                            <div class="calendar-day {{ $entry ? 'has-entry' : '' }} {{ $isToday ? 'today' : '' }} {{ !$isCurrentMonth ? 'other-month' : '' }}"
                                 @if($entry) onclick="openEditEntryModal({{ json_encode($entry) }})" @elseif($isCurrentMonth) onclick="openAddEntryModal('{{ $dateKey }}')" @endif>
                                <span class="day-number">{{ $startDay->day }}</span>
                                @if($entry && $entry->meetsStepGoal())
                                    <span class="goal-achieved"><i class="fas fa-check"></i></span>
                                @endif
                            </div>
                            @php $startDay->addDay(); @endphp
                        @endwhile
                    </div>

                    <div style="margin-top: 1rem; font-size: 0.75rem; color: var(--text-secondary); display: flex; gap: 1rem;">
                        <span><span style="display: inline-block; width: 8px; height: 8px; background: rgba(16, 185, 129, 0.3); border-radius: 2px; margin-right: 4px;"></span> Has entry</span>
                        <span><i class="fas fa-check" style="color: #10b981; font-size: 0.65rem;"></i> Goal met</span>
                    </div>
                </div>
            </div>

            <!-- Recent Entries Table -->
            <div class="card">
            <div class="card-header">
                <h3>Recent Entries</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Steps</th>
                                <th>Avg HR</th>
                                <th>Resting HR</th>
                                <th>Goal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                <tr>
                                    <td>
                                        <strong>{{ $entry->date->format('d M Y') }}</strong>
                                        <br>
                                        <small style="color: #6b7280;">{{ $entry->date->format('l') }}</small>
                                    </td>
                                    <td>
                                        <span class="health-metric-value steps">{{ $entry->formatted_steps }}</span>
                                    </td>
                                    <td>
                                        <span class="health-metric-value heart-rate">{{ $entry->formatted_avg_heart_rate }}</span>
                                    </td>
                                    <td>
                                        <span class="health-metric-value resting-hr">{{ $entry->formatted_resting_heart_rate }}</span>
                                    </td>
                                    <td>
                                        @if($entry->meetsStepGoal())
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Met</span>
                                        @elseif($entry->steps)
                                            <span class="badge badge-warning">{{ number_format(($entry->steps / $stepGoal) * 100, 0) }}%</span>
                                        @else
                                            <span class="badge badge-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="openEditEntryModal({{ json_encode($entry) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteEntry({{ $entry->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1rem;">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
        </div>
    @endif
</div>

<!-- Export Modal -->
<div class="mood-popup-overlay" id="exportModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Export Health Data</h3>
            <button class="mood-popup-close" onclick="closeExportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.875rem;">
                Exports a CSV file with your steps, heart rate, and notes — ready to paste into any AI tool.
            </p>
            <form id="exportForm" action="{{ route('health.export') }}" method="GET">
                <div class="form-group">
                    <label for="exportStartDate">Start date</label>
                    <input type="date" id="exportStartDate" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="exportEndDate">End date</label>
                    <input type="date" id="exportEndDate" name="end_date" class="form-control" required>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeExportModal()" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-download"></i>
                        Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Entry Modal -->
<div class="mood-popup-overlay" id="entryModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3 id="modalTitle">Add Health Entry</h3>
            <button class="mood-popup-close" onclick="closeEntryModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <form id="entryForm">
                <input type="hidden" id="entryId" value="">

                <div class="form-group">
                    <label for="entryDate">Date</label>
                    <input type="date" id="entryDate" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="entrySteps">Steps</label>
                    <input type="number" id="entrySteps" class="form-control" placeholder="e.g. 10000" min="0" max="100000">
                </div>

                <div class="form-group">
                    <label for="entryAvgHr">Average Heart Rate (bpm)</label>
                    <input type="number" id="entryAvgHr" class="form-control" placeholder="e.g. 75" min="30" max="250">
                </div>

                <div class="form-group">
                    <label for="entryRestingHr">Resting Heart Rate (bpm)</label>
                    <input type="number" id="entryRestingHr" class="form-control" placeholder="e.g. 60" min="30" max="150">
                </div>

                <div class="form-group">
                    <label for="entryNotes">Notes (optional)</label>
                    <textarea id="entryNotes" class="form-control" rows="3" placeholder="Any notes about today..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeEntryModal()" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i>
                        <span id="submitBtnText">Save Entry</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openExportModal() {
    const today = '{{ now()->format('Y-m-d') }}';
    const firstOfMonth = '{{ now()->startOfMonth()->format('Y-m-d') }}';
    document.getElementById('exportStartDate').value = firstOfMonth;
    document.getElementById('exportEndDate').value = today;
    document.getElementById('exportModal').classList.add('active');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.remove('active');
}

document.getElementById('exportModal').addEventListener('click', (e) => {
    if (e.target.id === 'exportModal') {
        closeExportModal();
    }
});

let isEditing = false;
let editingId = null;

function openAddEntryModal(date = null) {
    isEditing = false;
    editingId = null;

    document.getElementById('modalTitle').textContent = 'Add Health Entry';
    document.getElementById('submitBtnText').textContent = 'Save Entry';
    document.getElementById('entryId').value = '';
    document.getElementById('entryDate').value = date || '{{ now()->format('Y-m-d') }}';
    document.getElementById('entrySteps').value = '';
    document.getElementById('entryAvgHr').value = '';
    document.getElementById('entryRestingHr').value = '';
    document.getElementById('entryNotes').value = '';

    document.getElementById('entryModal').classList.add('active');
    setTimeout(() => document.getElementById('entryDate').focus(), 100);
}

function openEditEntryModal(entry) {
    isEditing = true;
    editingId = entry.id;

    document.getElementById('modalTitle').textContent = 'Edit Health Entry';
    document.getElementById('submitBtnText').textContent = 'Update Entry';
    document.getElementById('entryId').value = entry.id;
    document.getElementById('entryDate').value = entry.date.split('T')[0];
    document.getElementById('entrySteps').value = entry.steps || '';
    document.getElementById('entryAvgHr').value = entry.avg_heart_rate || '';
    document.getElementById('entryRestingHr').value = entry.resting_heart_rate || '';
    document.getElementById('entryNotes').value = entry.notes || '';

    document.getElementById('entryModal').classList.add('active');
}

function closeEntryModal() {
    document.getElementById('entryModal').classList.remove('active');
    isEditing = false;
    editingId = null;
}

document.getElementById('entryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        date: document.getElementById('entryDate').value,
        steps: document.getElementById('entrySteps').value || null,
        avg_heart_rate: document.getElementById('entryAvgHr').value || null,
        resting_heart_rate: document.getElementById('entryRestingHr').value || null,
        notes: document.getElementById('entryNotes').value || null,
    };

    const url = isEditing ? `{{ url('/health') }}/${editingId}` : '{{ route('health.store') }}';
    const method = isEditing ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message);
            closeEntryModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to save entry', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to save entry', 'error');
    });
});

function deleteEntry(id) {
    if (!confirm('Are you sure you want to delete this entry?')) {
        return;
    }

    fetch(`{{ url('/health') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to delete entry', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete entry', 'error');
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
    setTimeout(() => notification.remove(), 3000);
    return notification;
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('entryModal');
    const isModalOpen = modal.classList.contains('active');
    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);

    const isExportModalOpen = document.getElementById('exportModal').classList.contains('active');

    // Close modal on Escape
    if (e.key === 'Escape' && isModalOpen) {
        closeEntryModal();
    }

    if (e.key === 'Escape' && isExportModalOpen) {
        closeExportModal();
    }

    // Open modal on 'o' key (only when not typing and modal is closed)
    if (e.key === 'o' && !isModalOpen && !isTyping) {
        e.preventDefault();
        openAddEntryModal();
    }
});

// Close modal on overlay click
document.getElementById('entryModal').addEventListener('click', (e) => {
    if (e.target.id === 'entryModal') {
        closeEntryModal();
    }
});
</script>

<style>
.health-accent {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.health-metric-value {
    font-weight: 600;
}
.health-metric-value.steps { color: #10b981; }
.health-metric-value.heart-rate { color: #ef4444; }
.health-metric-value.resting-hr { color: #f59e0b; }

.badge-success {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}
.badge-warning {
    background: #f59e0b;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}
.badge-muted {
    background: var(--badge-muted-bg, #6b7280);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
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
