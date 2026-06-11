@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Uitgaven</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Uitgaven</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('expenses.stats') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i>
                    Statistieken
                </a>
                <a href="{{ route('expenses.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-tags"></i>
                    Categorieën
                </a>
                <button onclick="openAddExpenseModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Uitgave toevoegen
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon expense-accent">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['this_week_formatted'] }}</div>
                <div class="stat-label">Deze week</div>
                <div class="stat-change {{ $stats['change_class'] }}">{{ $stats['change_text'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon expense-accent">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['this_month_formatted'] }}</div>
                <div class="stat-label">Deze maand</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['avg_per_day_formatted'] }}</div>
                <div class="stat-label">Gem. per dag</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($stats['total_expenses']) }}</div>
                <div class="stat-label">Totaal uitgaven</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('expenses.index') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                    <label for="category" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Categorie</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">Alle categorieën</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                    <label for="start_date" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Van</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                    <label for="end_date" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Tot</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['category', 'start_date', 'end_date']))
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($expenses->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-wallet" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">Nog geen uitgaven</h3>
                <p style="color: #6b7280;">Begin met het bijhouden van je uitgaven door je eerste uitgave toe te voegen.</p>
                @if($categories->isEmpty())
                    <a href="{{ route('expenses.categories.index') }}" class="btn btn-secondary" style="margin-top: 1rem;">
                        <i class="fas fa-tags"></i>
                        Maak eerst categorieën aan
                    </a>
                @else
                    <button onclick="openAddExpenseModal()" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i>
                        Eerste uitgave toevoegen
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3>Uitgaven</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Beschrijving</th>
                                <th>Categorie</th>
                                <th style="text-align: right;">Bedrag</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $expense)
                                <tr>
                                    <td>
                                        <strong>{{ $expense->date->format('d M Y') }}</strong>
                                        <br>
                                        <small style="color: #6b7280;">{{ $expense->date->format('l') }}</small>
                                    </td>
                                    <td>
                                        {{ $expense->description ?: '-' }}
                                        @if($expense->notes)
                                            <br>
                                            <small style="color: #6b7280;">{{ Str::limit($expense->notes, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="expense-category-badge" style="background-color: {{ $expense->category->color }}20; color: {{ $expense->category->color }}; border: 1px solid {{ $expense->category->color }}40;">
                                            <i class="fas {{ $expense->category->icon }}"></i>
                                            {{ $expense->category->name }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="expense-amount">{{ $expense->formatted_amount }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="openEditExpenseModal({{ json_encode($expense) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteExpense({{ $expense->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1rem;">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Add/Edit Expense Modal -->
<div class="mood-popup-overlay" id="expenseModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3 id="modalTitle">Uitgave toevoegen</h3>
            <button class="mood-popup-close" onclick="closeExpenseModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <form id="expenseForm">
                <input type="hidden" id="expenseId" value="">

                <div class="form-group">
                    <label for="expenseCategory">Categorie</label>
                    <select id="expenseCategory" class="form-control" required>
                        <option value="">Selecteer categorie...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="expenseAmount">Bedrag</label>
                    <input type="number" id="expenseAmount" class="form-control" placeholder="0.00" min="0.01" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="expenseDate">Datum</label>
                    <input type="date" id="expenseDate" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="expenseDescription">Beschrijving (optioneel)</label>
                    <input type="text" id="expenseDescription" class="form-control" placeholder="Wat heb je gekocht?">
                </div>

                <div class="form-group">
                    <label for="expenseNotes">Notities (optioneel)</label>
                    <textarea id="expenseNotes" class="form-control" rows="2" placeholder="Extra notities..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeExpenseModal()" style="flex: 1;">Annuleren</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i>
                        <span id="submitBtnText">Opslaan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let isEditing = false;
let editingId = null;

document.addEventListener('DOMContentLoaded', function () {
    if (new URLSearchParams(window.location.search).get('modal') === 'open') {
        openAddExpenseModal();
        history.replaceState(null, '', window.location.pathname);
    }
});

function openAddExpenseModal() {
    @if($categories->isEmpty())
        showNotification('Maak eerst een categorie aan', 'error');
        return;
    @endif

    isEditing = false;
    editingId = null;

    document.getElementById('modalTitle').textContent = 'Uitgave toevoegen';
    document.getElementById('submitBtnText').textContent = 'Opslaan';
    document.getElementById('expenseId').value = '';
    document.getElementById('expenseCategory').value = '';
    document.getElementById('expenseAmount').value = '';
    const savedMonth = localStorage.getItem('lastExpenseMonth');
    document.getElementById('expenseDate').value = savedMonth
        ? savedMonth + '-01'
        : '{{ now()->format('Y-m-d') }}';
    document.getElementById('expenseDescription').value = '';
    document.getElementById('expenseNotes').value = '';

    document.getElementById('expenseModal').classList.add('active');
    setTimeout(() => document.getElementById('expenseCategory').focus(), 100);
}

function openEditExpenseModal(expense) {
    isEditing = true;
    editingId = expense.id;

    document.getElementById('modalTitle').textContent = 'Uitgave bewerken';
    document.getElementById('submitBtnText').textContent = 'Bijwerken';
    document.getElementById('expenseId').value = expense.id;
    document.getElementById('expenseCategory').value = expense.expense_category_id;
    document.getElementById('expenseAmount').value = expense.amount;
    document.getElementById('expenseDate').value = expense.date.split('T')[0];
    document.getElementById('expenseDescription').value = expense.description || '';
    document.getElementById('expenseNotes').value = expense.notes || '';

    document.getElementById('expenseModal').classList.add('active');
}

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.remove('active');
    isEditing = false;
    editingId = null;
}

document.getElementById('expenseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        expense_category_id: document.getElementById('expenseCategory').value,
        amount: document.getElementById('expenseAmount').value,
        date: document.getElementById('expenseDate').value,
        description: document.getElementById('expenseDescription').value || null,
        notes: document.getElementById('expenseNotes').value || null,
    };

    const url = isEditing ? `{{ url('/expenses') }}/${editingId}` : '{{ route('expenses.store') }}';
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
            closeExpenseModal();
            if (isEditing) {
                setTimeout(() => window.location.reload(), 1000);
            } else {
                const lastDate = document.getElementById('expenseDate').value;
                if (lastDate) {
                    localStorage.setItem('lastExpenseMonth', lastDate.substring(0, 7));
                }
                setTimeout(() => window.location.href = '{{ route('expenses.index') }}?modal=open', 1000);
            }
        } else {
            showNotification(data.message || 'Fout bij opslaan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Fout bij opslaan', 'error');
    });
});

function deleteExpense(id) {
    if (!confirm('Weet je zeker dat je deze uitgave wilt verwijderen?')) {
        return;
    }

    fetch(`{{ url('/expenses') }}/${id}`, {
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
            showNotification(data.message || 'Fout bij verwijderen', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Fout bij verwijderen', 'error');
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
    const modal = document.getElementById('expenseModal');
    const isModalOpen = modal.classList.contains('active');
    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);

    if (e.key === 'Escape' && isModalOpen) {
        closeExpenseModal();
    }

    if (e.key === 'o' && !isModalOpen && !isTyping) {
        e.preventDefault();
        openAddExpenseModal();
    }
});

document.getElementById('expenseModal').addEventListener('click', (e) => {
    if (e.target.id === 'expenseModal') {
        closeExpenseModal();
    }
});
</script>

<style>
.expense-accent {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.expense-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.expense-amount {
    font-weight: 600;
    color: #ef4444;
    font-size: 1rem;
}

.stat-change {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}
.stat-change.positive { color: #10b981; }
.stat-change.negative { color: #ef4444; }
.stat-change.neutral { color: #6b7280; }

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
