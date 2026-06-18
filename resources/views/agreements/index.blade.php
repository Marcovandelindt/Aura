@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Afspraken & Doelen</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Afspraken & Doelen</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="openModal('goal-modal')">
                    <i class="fas fa-flag"></i> Nieuw doel
                </button>
                <button type="button" class="btn btn-primary" onclick="openModal('agreement-modal')">
                    <i class="fas fa-plus"></i> Nieuwe afspraak
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    {{-- Subtle link to personal insights --}}
    <div style="margin-bottom: 2rem; display: flex; justify-content: flex-end;">
        <a href="{{ route('insights.index') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-muted); text-decoration: none; opacity: 0.7; transition: opacity 0.15s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <i class="fas fa-seedling" style="font-size: 0.75rem;"></i>
            Inzichten over mezelf
        </a>
    </div>

    {{-- Goals --}}
    @if($goals->isNotEmpty())
        <div style="margin-bottom: 2rem;">
            <h2 style="font-size: 1rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">Doelen</h2>
            <div class="goals-grid">
                @foreach($goals as $goal)
                    <div class="goal-card">
                        <div class="goal-card-header">
                            <div class="goal-icon">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="goal-title">{{ $goal->title }}</div>
                                @if($goal->target_date)
                                    <div class="goal-date">
                                        <i class="fas fa-calendar" style="margin-right: 0.3rem;"></i>
                                        {{ $goal->target_date->locale('nl')->translatedFormat('d F Y') }}
                                        @if($goal->days_remaining !== null)
                                            <span style="margin-left: 0.4rem; opacity: 0.7;">({{ $goal->days_remaining }} dagen)</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div style="flex-shrink: 0;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openGoalStatusModal({{ $goal->id }}, '{{ $goal->status }}')">
                                    <span class="goal-status-badge goal-status-{{ $goal->status }}">{{ $goal->status_label }}</span>
                                </button>
                            </div>
                        </div>
                        @if($goal->description)
                            <p class="goal-description">{{ $goal->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Agreements --}}
    <div>
        <h2 style="font-size: 1rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
            Afspraken met mezelf
        </h2>

        @if($agreements->isEmpty())
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-handshake" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">Nog geen afspraken</h3>
                    <p style="color: var(--text-muted);">Voeg je eerste afspraak toe — een concrete grens of gewoonte die je wilt respecteren.</p>
                    <button type="button" class="btn btn-primary" style="margin-top: 1rem;" onclick="openModal('agreement-modal')">
                        <i class="fas fa-plus"></i> Eerste afspraak toevoegen
                    </button>
                </div>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($agreements as $agreement)
                    @php
                        $checkIns = $agreement->getLast30DaysCheckIns()->keyBy(fn($c) => $c->checked_on->format('Y-m-d'));
                        $todayStatus = $todayCheckIns->get($agreement->id);
                    @endphp
                    <div class="agreement-card">
                        <div class="agreement-card-header">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; flex-wrap: wrap;">
                                    <span class="agreement-title">{{ $agreement->title }}</span>
                                    <span class="category-badge category-{{ $agreement->category }}">{{ $agreement->category_label }}</span>
                                </div>
                                @if($agreement->description)
                                    <p class="agreement-description">{{ $agreement->description }}</p>
                                @endif
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                                @if($todayStatus)
                                    <span class="checkin-status checkin-{{ $todayStatus }}">
                                        @if($todayStatus === 'respected') <i class="fas fa-check"></i> Gehouden
                                        @elseif($todayStatus === 'partially') <i class="fas fa-minus"></i> Deels
                                        @else <i class="fas fa-times"></i> Niet gehouden
                                        @endif
                                    </span>
                                @else
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openCheckInModal({{ $agreement->id }}, '{{ addslashes($agreement->title) }}')">
                                        <i class="fas fa-clipboard-check"></i> Check-in
                                    </button>
                                @endif
                                <form method="POST" action="{{ route('agreements.destroy', $agreement) }}" onsubmit="return confirm('Afspraak deactiveren?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Deactiveren">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- 30-day heatmap --}}
                        <div class="heatmap">
                            @for($i = 29; $i >= 0; $i--)
                                @php
                                    $date = now()->subDays($i);
                                    $dateKey = $date->format('Y-m-d');
                                    $checkIn = $checkIns->get($dateKey);
                                    $isToday = $i === 0;
                                @endphp
                                <div class="heatmap-day {{ $checkIn ? 'heatmap-' . $checkIn->status : 'heatmap-empty' }} {{ $isToday ? 'heatmap-today' : '' }}"
                                     title="{{ $date->locale('nl')->translatedFormat('d M') }}{{ $checkIn ? ': ' . $checkIn->status_label : '' }}{{ $checkIn && $checkIn->notes ? ' — ' . $checkIn->notes : '' }}">
                                </div>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Agreement Modal --}}
<div class="mood-popup-overlay" id="agreement-modal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Nieuwe afspraak</h3>
            <button type="button" class="mood-popup-close" onclick="closeModal('agreement-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('agreements.store') }}">
            @csrf
            <div class="mood-popup-content">
                <div class="form-group">
                    <label>Afspraak</label>
                    <input type="text" name="title" class="form-control" placeholder="Bijv. Ik check Slack niet na 18:00" required>
                </div>
                <div class="form-group">
                    <label>Toelichting <span style="color: var(--text-muted); font-weight: normal;">(optioneel)</span></label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Waarom is deze afspraak belangrijk?"></textarea>
                </div>
                <div class="form-group">
                    <label>Categorie</label>
                    <select name="category" class="form-control" required>
                        <option value="bereikbaarheid">Bereikbaarheid</option>
                        <option value="faalangst">Faalangst</option>
                        <option value="verantwoordelijkheid">Verantwoordelijkheid</option>
                        <option value="perfectionisme">Perfectionisme</option>
                        <option value="overig">Overig</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('agreement-modal')">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Toevoegen</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Check-in Modal --}}
<div class="mood-popup-overlay" id="checkin-modal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3 id="checkin-modal-title">Check-in</h3>
            <button type="button" class="mood-popup-close" onclick="closeModal('checkin-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="checkin-form">
            @csrf
            <input type="hidden" name="checked_on" value="{{ now()->format('Y-m-d') }}">
            <div class="mood-popup-content">
                <p style="color: var(--text-muted); margin-bottom: 1.25rem; font-size: 0.9rem;">Hoe ging het vandaag met deze afspraak?</p>
                <div class="checkin-options">
                    <label class="checkin-option">
                        <input type="radio" name="status" value="respected" required>
                        <span class="checkin-option-btn checkin-option-respected">
                            <i class="fas fa-check"></i>
                            <span>Gehouden</span>
                        </span>
                    </label>
                    <label class="checkin-option">
                        <input type="radio" name="status" value="partially">
                        <span class="checkin-option-btn checkin-option-partially">
                            <i class="fas fa-minus"></i>
                            <span>Deels</span>
                        </span>
                    </label>
                    <label class="checkin-option">
                        <input type="radio" name="status" value="not_respected">
                        <span class="checkin-option-btn checkin-option-not_respected">
                            <i class="fas fa-times"></i>
                            <span>Niet gehouden</span>
                        </span>
                    </label>
                </div>
                <div class="form-group" style="margin-top: 1.25rem;">
                    <label>Notitie <span style="color: var(--text-muted); font-weight: normal;">(optioneel)</span></label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Wat maakte het makkelijk of moeilijk?"></textarea>
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('checkin-modal')">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Goal Modal --}}
<div class="mood-popup-overlay" id="goal-modal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Nieuw doel</h3>
            <button type="button" class="mood-popup-close" onclick="closeModal('goal-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('goals.store') }}">
            @csrf
            <div class="mood-popup-content">
                <div class="form-group">
                    <label>Doel</label>
                    <input type="text" name="title" class="form-control" placeholder="Bijv. Terugkeren bij Fresh-Dev op een gezonde manier" required>
                </div>
                <div class="form-group">
                    <label>Beschrijving <span style="color: var(--text-muted); font-weight: normal;">(optioneel)</span></label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Wat betekent dit doel voor jou?"></textarea>
                </div>
                <div class="form-group">
                    <label>Streefdatum <span style="color: var(--text-muted); font-weight: normal;">(optioneel)</span></label>
                    <input type="date" name="target_date" class="form-control">
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('goal-modal')">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Toevoegen</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Goal Status Modal --}}
<div class="mood-popup-overlay" id="goal-status-modal">
    <div class="mood-popup" style="max-width: 380px;">
        <div class="mood-popup-header">
            <h3>Status bijwerken</h3>
            <button type="button" class="mood-popup-close" onclick="closeModal('goal-status-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="goal-status-form">
            @csrf @method('PATCH')
            <div class="mood-popup-content">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" id="goal-status-select">
                        <option value="active">Actief</option>
                        <option value="achieved">Behaald</option>
                        <option value="abandoned">Gestopt</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('goal-status-modal')">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.goal-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.25rem;
}

.goal-card-header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.goal-icon {
    width: 2.25rem;
    height: 2.25rem;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.goal-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.2rem;
}

.goal-date {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.goal-description {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 0.75rem 0 0;
    line-height: 1.5;
}

.goal-status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
}

.goal-status-active { background: #6366f120; color: #6366f1; }
.goal-status-achieved { background: #10b98120; color: #10b981; }
.goal-status-abandoned { background: #6b728020; color: #6b7280; }

.agreement-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.25rem;
}

.agreement-card-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.agreement-title {
    font-weight: 600;
    font-size: 0.95rem;
}

.agreement-description {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 0.25rem 0 0;
    line-height: 1.5;
}

.category-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    white-space: nowrap;
}

.category-bereikbaarheid { background: #3b82f620; color: #3b82f6; }
.category-faalangst { background: #f59e0b20; color: #f59e0b; }
.category-verantwoordelijkheid { background: #8b5cf620; color: #8b5cf6; }
.category-perfectionisme { background: #ec489920; color: #ec4899; }
.category-overig { background: #6b728020; color: #6b7280; }

.checkin-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
}

.checkin-respected { background: #10b98120; color: #10b981; }
.checkin-partially { background: #f59e0b20; color: #f59e0b; }
.checkin-not_respected { background: #ef444420; color: #ef4444; }

.heatmap {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.heatmap-day {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    cursor: default;
    transition: transform 0.1s;
}

.heatmap-day:hover {
    transform: scale(1.3);
}

.heatmap-empty { background: var(--border-color); }
.heatmap-respected { background: #10b981; }
.heatmap-partially { background: #f59e0b; }
.heatmap-not_respected { background: #ef4444; }
.heatmap-today { outline: 2px solid var(--text-muted); outline-offset: 1px; }

.checkin-options {
    display: flex;
    gap: 0.75rem;
}

.checkin-option {
    flex: 1;
    cursor: pointer;
}

.checkin-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.checkin-option-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 0.9rem 0.5rem;
    border-radius: 0.5rem;
    border: 2px solid var(--border-color);
    font-size: 0.85rem;
    font-weight: 600;
    transition: border-color 0.15s, background 0.15s;
    cursor: pointer;
}

.checkin-option input[type="radio"]:checked + .checkin-option-btn.checkin-option-respected {
    border-color: #10b981;
    background: #10b98115;
    color: #10b981;
}

.checkin-option input[type="radio"]:checked + .checkin-option-btn.checkin-option-partially {
    border-color: #f59e0b;
    background: #f59e0b15;
    color: #f59e0b;
}

.checkin-option input[type="radio"]:checked + .checkin-option-btn.checkin-option-not_respected {
    border-color: #ef4444;
    background: #ef444415;
    color: #ef4444;
}
</style>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function openCheckInModal(agreementId, title) {
    document.getElementById('checkin-modal-title').textContent = title;
    document.getElementById('checkin-form').action = `/agreements/${agreementId}/check-in`;
    document.querySelectorAll('#checkin-form input[name="status"]').forEach(r => r.checked = false);
    document.querySelector('#checkin-form textarea[name="notes"]').value = '';
    openModal('checkin-modal');
}

function openGoalStatusModal(goalId, currentStatus) {
    document.getElementById('goal-status-form').action = `/goals/${goalId}`;
    document.getElementById('goal-status-select').value = currentStatus;
    openModal('goal-status-modal');
}

document.querySelectorAll('.mood-popup-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.mood-popup-overlay.active').forEach(el => el.classList.remove('active'));
    }
});
</script>
@endsection
