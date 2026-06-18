@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Taken</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Taken</li>
                </ul>
            </div>
            <button type="button" class="btn btn-primary" onclick="openModal('task-modal')">
                <i class="fas fa-plus"></i> Nieuwe taak
            </button>
        </div>
    </div>

    {{-- XP popup --}}
    @if(session('xp_earned'))
        <div class="xp-popup" id="xp-popup">
            +{{ session('xp_earned') }} XP
            @if(session('leveled_up')) &nbsp;🎉 Level up! @endif
        </div>
    @endif

    {{-- Achievement popup --}}
    @if(session('new_achievements') && count(session('new_achievements')))
        @foreach(session('new_achievements') as $achievement)
            <div class="achievement-popup" id="achievement-popup-{{ $achievement->id }}">
                <div class="achievement-popup-icon">{{ $achievement->icon }}</div>
                <div>
                    <div class="achievement-popup-title">Achievement ontgrendeld!</div>
                    <div class="achievement-popup-name">{{ $achievement->name }}</div>
                    <div class="achievement-popup-xp">+{{ $achievement->xp_bonus }} XP</div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Stats bar --}}
    <div class="stats-bar">
        <div class="stats-level">
            <div class="level-badge">{{ $stats->level }}</div>
            <div>
                <div class="level-name">{{ $stats->levelName() }}</div>
                <div class="level-sub">Level {{ $stats->level }}</div>
            </div>
        </div>
        <div class="stats-xp">
            <div class="xp-bar-header">
                <span>{{ $stats->xpIntoCurrentLevel() }} / {{ $stats->xpForNextLevel() }} XP</span>
                <span style="color: var(--text-muted); font-size: 0.75rem;">naar level {{ $stats->level + 1 }}</span>
            </div>
            <div class="xp-bar-track">
                <div class="xp-bar-fill" style="width: {{ $stats->xpProgressPercent() }}%"></div>
            </div>
        </div>
        <div class="stats-streak">
            <div class="streak-count">🔥 {{ $stats->current_streak }}</div>
            <div class="streak-label">dag streak</div>
        </div>
        <div class="stats-total">
            <div class="total-count">{{ $stats->tasks_completed }}</div>
            <div class="total-label">afgerond</div>
        </div>
    </div>

    {{-- Pending tasks --}}
    <div style="margin-bottom: 2rem;">
        <h2 class="section-title">Open taken</h2>

        @if($pendingTasks->isEmpty())
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                    <h3 style="margin-bottom: 0.5rem;">Alles gedaan!</h3>
                    <p style="color: var(--text-muted);">Geen open taken. Voeg een nieuwe toe of geniet van je rust.</p>
                </div>
            </div>
        @else
            <div class="tasks-list">
                @foreach($pendingTasks as $task)
                    <div class="task-card difficulty-{{ $task->difficulty }}">
                        <div class="task-card-main">
                            <form method="POST" action="{{ route('tasks.complete', $task) }}" style="flex-shrink: 0;">
                                @csrf
                                <button type="submit" class="complete-btn" title="Voltooien">
                                    <i class="far fa-circle"></i>
                                </button>
                            </form>
                            <div class="task-info">
                                <div class="task-title">{{ $task->title }}</div>
                                @if($task->description)
                                    <div class="task-description">{{ $task->description }}</div>
                                @endif
                                <div class="task-meta">
                                    <span class="task-badge category-badge-{{ $task->category }}">{{ $task->categoryLabel() }}</span>
                                    <span class="task-badge difficulty-badge-{{ $task->difficulty }}">{{ $task->difficultyLabel() }}</span>
                                    @if($task->type !== 'once')
                                        <span class="task-badge type-badge">
                                            <i class="fas fa-sync" style="font-size: 0.6rem;"></i>
                                            {{ $task->type === 'daily' ? 'Dagelijks' : 'Wekelijks' }}
                                        </span>
                                    @endif
                                    @if($task->due_date)
                                        <span class="task-badge due-badge">
                                            <i class="fas fa-calendar" style="font-size: 0.6rem;"></i>
                                            {{ $task->due_date->locale('nl')->translatedFormat('d M') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="task-xp">+{{ $task->xpReward() }} XP</div>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Verwijderen?');" style="flex-shrink: 0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="task-delete-btn" title="Verwijderen">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Overdue tasks --}}
    @if($overdueTasks->isNotEmpty())
        <div style="margin-bottom: 2rem;">
            <h2 class="section-title section-title--overdue">Verlopen</h2>
            <div class="tasks-list">
                @foreach($overdueTasks as $task)
                    <div class="task-card difficulty-{{ $task->difficulty }} task-card--overdue">
                        <div class="task-card-main">
                            <form method="POST" action="{{ route('tasks.complete', $task) }}" style="flex-shrink: 0;">
                                @csrf
                                <button type="submit" class="complete-btn" title="Voltooien">
                                    <i class="far fa-circle"></i>
                                </button>
                            </form>
                            <div class="task-info">
                                <div class="task-title">{{ $task->title }}</div>
                                @if($task->description)
                                    <div class="task-description">{{ $task->description }}</div>
                                @endif
                                <div class="task-meta">
                                    <span class="task-badge category-badge-{{ $task->category }}">{{ $task->categoryLabel() }}</span>
                                    <span class="task-badge difficulty-badge-{{ $task->difficulty }}">{{ $task->difficultyLabel() }}</span>
                                    <span class="task-badge" style="background: #ef444420; color: #ef4444;">
                                        <i class="fas fa-calendar-xmark" style="font-size: 0.6rem;"></i>
                                        {{ $task->due_date->locale('nl')->translatedFormat('d M') }}
                                    </span>
                                </div>
                            </div>
                            <div class="task-xp">+{{ $task->xpReward() }} XP</div>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Verwijderen?');" style="flex-shrink: 0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="task-delete-btn" title="Verwijderen">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Upcoming tasks --}}
    @if($upcomingTasks->isNotEmpty())
        <div style="margin-bottom: 2rem;">
            <h2 class="section-title">Aankomend</h2>
            <div class="tasks-list tasks-list--upcoming">
                @foreach($upcomingTasks as $task)
                    <div class="task-card task-card--upcoming">
                        <div class="task-card-main">
                            <div class="complete-btn complete-btn--upcoming">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="task-info">
                                <div class="task-title">{{ $task->title }}</div>
                                <div class="task-meta">
                                    <span class="task-badge category-badge-{{ $task->category }}">{{ $task->categoryLabel() }}</span>
                                    <span class="task-badge difficulty-badge-{{ $task->difficulty }}">{{ $task->difficultyLabel() }}</span>
                                    <span class="task-badge due-badge">
                                        <i class="fas fa-calendar" style="font-size: 0.6rem;"></i>
                                        {{ $task->due_date->locale('nl')->translatedFormat('d M') }}
                                    </span>
                                </div>
                            </div>
                            <div class="task-xp" style="opacity: 0.4;">+{{ $task->xpReward() }} XP</div>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Verwijderen?');" style="flex-shrink: 0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="task-delete-btn" title="Verwijderen">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Completed today --}}
    @if($completedToday->isNotEmpty())
        <div style="margin-bottom: 2rem;">
            <h2 class="section-title">Vandaag afgerond</h2>
            <div class="tasks-list tasks-list--done">
                @foreach($completedToday as $task)
                    <div class="task-card task-card--done">
                        <div class="task-card-main">
                            <form method="POST" action="{{ route('tasks.uncomplete', $task) }}" style="flex-shrink: 0;">
                                @csrf
                                <button type="submit" class="complete-btn complete-btn--done" title="Terugzetten">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <div class="task-info">
                                <div class="task-title task-title--done">{{ $task->title }}</div>
                                <div class="task-meta">
                                    <span class="task-badge category-badge-{{ $task->category }}">{{ $task->categoryLabel() }}</span>
                                </div>
                            </div>
                            <div class="task-xp task-xp--done">+{{ $task->xpReward() }} XP</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Achievements --}}
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h2 class="section-title" style="margin-bottom: 0;">Achievements</h2>
            <form method="POST" action="{{ route('tasks.reset-progress') }}" onsubmit="return confirm('Weet je zeker dat je alle voortgang en achievements wilt resetten?');">
                @csrf
                <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 0.75rem; color: var(--text-muted); opacity: 0.4; transition: opacity 0.15s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='0.4'">
                    <i class="fas fa-rotate-left" style="margin-right: 0.25rem;"></i> Reset voortgang
                </button>
            </form>
        </div>
        <div class="achievements-grid" style="margin-top: 0;">
            @foreach($achievements as $achievement)
                <div class="achievement-card {{ $achievement->unlocked ? 'achievement-card--unlocked' : 'achievement-card--locked' }}">
                    <div class="achievement-icon">{{ $achievement->icon }}</div>
                    <div class="achievement-name">{{ $achievement->name }}</div>
                    <div class="achievement-desc">{{ $achievement->description }}</div>
                    <div class="achievement-xp">+{{ $achievement->xp_bonus }} XP</div>
                    @if($achievement->unlocked)
                        <div class="achievement-date">{{ $achievement->unlocked->unlocked_at->locale('nl')->translatedFormat('d M Y') }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- New task modal --}}
<div class="mood-popup-overlay" id="task-modal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3>Nieuwe taak</h3>
            <button type="button" class="mood-popup-close" onclick="closeModal('task-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('tasks.store') }}">
            @csrf
            <div class="mood-popup-content">
                <div class="form-group">
                    <label>Taak</label>
                    <input type="text" name="title" class="form-control" placeholder="Bijv. Stofzuigen" required autofocus>
                </div>
                <div class="form-group">
                    <label>Omschrijving <span style="color: var(--text-muted); font-weight: normal;">(optioneel)</span></label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Categorie</label>
                        <select name="category" class="form-control" required>
                            <option value="huis">🏠 Huis</option>
                            <option value="beweging">🚶 Beweging</option>
                            <option value="welzijn">🧘 Welzijn</option>
                            <option value="gezondheid">❤️ Gezondheid</option>
                            <option value="persoonlijk">🎯 Persoonlijk</option>
                            <option value="werk">💼 Werk</option>
                            <option value="overig" selected>⭐ Overig</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Moeilijkheid</label>
                        <select name="difficulty" class="form-control" required>
                            <option value="easy">Makkelijk (10 XP)</option>
                            <option value="normal" selected>Normaal (25 XP)</option>
                            <option value="hard">Zwaar (60 XP)</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Herhaling</label>
                        <select name="type" class="form-control" required>
                            <option value="once" selected>Eenmalig</option>
                            <option value="daily">Dagelijks</option>
                            <option value="weekly">Wekelijks</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Deadline <span style="color: var(--text-muted); font-weight: normal;">(optioneel)</span></label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('task-modal')">Annuleren</button>
                    <button type="submit" class="btn btn-primary">Toevoegen</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.stats-bar {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.stats-level {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

.level-badge {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.1rem;
    color: white;
}

.level-name {
    font-weight: 700;
    font-size: 0.95rem;
}

.level-sub {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.stats-xp {
    flex: 1;
    min-width: 200px;
}

.xp-bar-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
}

.xp-bar-track {
    height: 8px;
    background: var(--border-color);
    border-radius: 999px;
    overflow: hidden;
}

.xp-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #d97706);
    border-radius: 999px;
    transition: width 0.6s ease;
}

.stats-streak, .stats-total {
    text-align: center;
    flex-shrink: 0;
}

.streak-count, .total-count {
    font-size: 1.4rem;
    font-weight: 800;
}

.streak-label, .total-label {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.task-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-left: 3px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    transition: border-color 0.15s;
}

.difficulty-easy { border-left-color: #10b981; }
.difficulty-normal { border-left-color: #f59e0b; }
.difficulty-hard { border-left-color: #ef4444; }

.task-card--done {
    opacity: 0.5;
    border-left-color: var(--border-color) !important;
}

.section-title--overdue {
    color: #ef4444;
}

.task-card--overdue {
    border-left-color: #ef4444 !important;
}

.task-card--upcoming {
    opacity: 0.6;
    border-left-color: #6366f1 !important;
}

.complete-btn--upcoming {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid var(--border-color);
    background: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 0.75rem;
    flex-shrink: 0;
}

.task-card-main {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.complete-btn {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid var(--border-color);
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 1rem;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
    flex-shrink: 0;
}

.complete-btn:hover {
    border-color: #10b981;
    color: #10b981;
    background: #10b98115;
}

.complete-btn--done {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid #10b981;
    background: #10b98120;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
    font-size: 0.85rem;
    flex-shrink: 0;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}

.complete-btn--done:hover {
    background: #ef444420;
    border-color: #ef4444;
    color: #ef4444;
}

.task-info {
    flex: 1;
    min-width: 0;
}

.task-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.task-title--done {
    text-decoration: line-through;
}

.task-description {
    font-size: 0.82rem;
    color: var(--text-muted);
    margin-bottom: 0.35rem;
}

.task-meta {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.task-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: var(--border-color);
    color: var(--text-muted);
}

.category-badge-huis { background: #3b82f620; color: #3b82f6; }
.category-badge-beweging { background: #10b98120; color: #10b981; }
.category-badge-welzijn { background: #8b5cf620; color: #8b5cf6; }
.category-badge-gezondheid { background: #ec489920; color: #ec4899; }
.category-badge-persoonlijk { background: #f59e0b20; color: #f59e0b; }
.category-badge-werk { background: #6366f120; color: #6366f1; }
.category-badge-overig { background: #6b728020; color: #6b7280; }

.difficulty-badge-easy { background: #10b98120; color: #10b981; }
.difficulty-badge-normal { background: #f59e0b20; color: #f59e0b; }
.difficulty-badge-hard { background: #ef444420; color: #ef4444; }

.type-badge { background: #6366f120; color: #6366f1; }
.due-badge { background: #ec489920; color: #ec4899; }

.task-xp {
    font-size: 0.8rem;
    font-weight: 700;
    color: #f59e0b;
    flex-shrink: 0;
}

.task-xp--done {
    opacity: 0.5;
}

.task-delete-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    opacity: 0.3;
    font-size: 0.8rem;
    padding: 0.2rem 0.4rem;
    transition: opacity 0.15s, color 0.15s;
}

.task-delete-btn:hover {
    opacity: 1;
    color: #ef4444;
}

.achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
}

.achievement-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    transition: transform 0.15s;
}

.achievement-card--unlocked {
    border-color: #f59e0b60;
    background: linear-gradient(135deg, #f59e0b08, var(--card-bg));
}

.achievement-card--locked {
    opacity: 0.4;
    filter: grayscale(1);
}

.achievement-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.achievement-name {
    font-weight: 700;
    font-size: 0.85rem;
    margin-bottom: 0.2rem;
}

.achievement-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.35rem;
}

.achievement-xp {
    font-size: 0.75rem;
    font-weight: 700;
    color: #f59e0b;
}

.achievement-date {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* XP popup */
.xp-popup {
    position: fixed;
    top: 5rem;
    right: 2rem;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    font-weight: 800;
    font-size: 1.2rem;
    padding: 0.75rem 1.5rem;
    border-radius: 999px;
    box-shadow: 0 4px 20px #f59e0b50;
    z-index: 9999;
    animation: xpFloat 2.5s ease forwards;
}

@keyframes xpFloat {
    0% { opacity: 0; transform: translateY(10px) scale(0.9); }
    20% { opacity: 1; transform: translateY(0) scale(1.05); }
    70% { opacity: 1; transform: translateY(-10px) scale(1); }
    100% { opacity: 0; transform: translateY(-30px) scale(0.95); }
}

/* Achievement popup */
.achievement-popup {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: var(--card-bg);
    border: 1px solid #f59e0b60;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 9999;
    animation: achievementSlide 4s ease forwards;
}

@keyframes achievementSlide {
    0% { opacity: 0; transform: translateX(30px); }
    15% { opacity: 1; transform: translateX(0); }
    75% { opacity: 1; transform: translateX(0); }
    100% { opacity: 0; transform: translateX(30px); }
}

.achievement-popup-icon {
    font-size: 2rem;
}

.achievement-popup-title {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 0.15rem;
}

.achievement-popup-name {
    font-weight: 700;
    font-size: 0.95rem;
}

.achievement-popup-xp {
    font-size: 0.8rem;
    font-weight: 700;
    color: #f59e0b;
}
</style>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.mood-popup-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.mood-popup-overlay.active').forEach(el => el.classList.remove('active'));
    }
});
</script>
@endsection
