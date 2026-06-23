@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Abonnementen</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('expenses.index') }}">Uitgaven</a></li>
                    <li>Abonnementen</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Terug
                </a>
                <button onclick="openAddSubscriptionModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Abonnement toevoegen
                </button>
            </div>
        </div>
    </div>

    @php
        $active = $subscriptions->where('is_active', true);
        $inactive = $subscriptions->where('is_active', false);
        $billingLabels = ['monthly' => 'Maandelijks', 'quarterly' => 'Per kwartaal', 'yearly' => 'Jaarlijks'];
    @endphp

    @if($subscriptions->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-receipt" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">Nog geen abonnementen</h3>
                <p style="color: #6b7280;">Voeg je vaste lasten toe zodat je ze snel kunt koppelen aan uitgaven.</p>
                <button onclick="openAddSubscriptionModal()" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Eerste abonnement toevoegen
                </button>
            </div>
        </div>
    @else
        @if($active->isNotEmpty())
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Actief</h3>
                </div>
                <div class="card-body">
                    <div class="subscriptions-grid">
                        @foreach($active as $subscription)
                            <div class="subscription-card">
                                <div class="subscription-info">
                                    <div class="subscription-name">{{ $subscription->name }}</div>
                                    <div class="subscription-meta">
                                        <span class="expense-category-badge" style="background-color: {{ $subscription->category->color }}20; color: {{ $subscription->category->color }}; border: 1px solid {{ $subscription->category->color }}40;">
                                            <i class="fas {{ $subscription->category->icon }}"></i>
                                            {{ $subscription->category->name }}
                                        </span>
                                        @if($subscription->subcategory)
                                            <span style="color: #6b7280; font-size: 0.75rem;">{{ $subscription->subcategory->name }}</span>
                                        @endif
                                    </div>
                                    <div class="subscription-billing">
                                        <span class="subscription-amount">{{ $subscription->formatted_amount }}</span>
                                        @if($subscription->original_amount)
                                            <span style="color: #9ca3af; font-size: 0.75rem; text-decoration: line-through;">€{{ number_format($subscription->original_amount, 2, ',', '.') }}</span>
                                        @endif
                                        <span style="color: #6b7280; font-size: 0.75rem;">{{ $billingLabels[$subscription->billing_cycle] }}</span>
                                        @if($subscription->billing_day)
                                            <span style="color: #6b7280; font-size: 0.75rem;">· dag {{ $subscription->billing_day }}</span>
                                        @endif
                                    </div>
                                    @if($subscription->notes)
                                        <div style="color: #6b7280; font-size: 0.75rem; font-style: italic;">{{ $subscription->notes }}</div>
                                    @endif
                                </div>
                                <div class="subscription-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="openEditSubscriptionModal({{ json_encode($subscription) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteSubscription({{ $subscription->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($inactive->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 style="color: #6b7280;">Inactief</h3>
                </div>
                <div class="card-body">
                    <div class="subscriptions-grid">
                        @foreach($inactive as $subscription)
                            <div class="subscription-card" style="opacity: 0.6;">
                                <div class="subscription-info">
                                    <div class="subscription-name">{{ $subscription->name }}</div>
                                    <div class="subscription-meta">
                                        <span class="expense-category-badge" style="background-color: {{ $subscription->category->color }}20; color: {{ $subscription->category->color }}; border: 1px solid {{ $subscription->category->color }}40;">
                                            <i class="fas {{ $subscription->category->icon }}"></i>
                                            {{ $subscription->category->name }}
                                        </span>
                                    </div>
                                    <div class="subscription-billing">
                                        <span class="subscription-amount">{{ $subscription->formatted_amount }}</span>
                                        <span style="color: #6b7280; font-size: 0.75rem;">{{ $billingLabels[$subscription->billing_cycle] }}</span>
                                    </div>
                                </div>
                                <div class="subscription-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="openEditSubscriptionModal({{ json_encode($subscription) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteSubscription({{ $subscription->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<!-- Add/Edit Subscription Modal -->
<div class="mood-popup-overlay" id="subscriptionModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3 id="modalTitle">Abonnement toevoegen</h3>
            <button class="mood-popup-close" onclick="closeSubscriptionModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <form id="subscriptionForm">
                <input type="hidden" id="subscriptionId" value="">

                <div class="form-group">
                    <label for="subName">Naam</label>
                    <input type="text" id="subName" class="form-control" placeholder="bijv. Netflix, Spotify" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="subAmount">Bedrag dat ik betaal</label>
                        <input type="number" id="subAmount" class="form-control" placeholder="0.00" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="subOriginalAmount">Originele prijs (optioneel)</label>
                        <input type="number" id="subOriginalAmount" class="form-control" placeholder="0.00" min="0.01" step="0.01">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="subBillingDay">Dag van de maand</label>
                        <input type="number" id="subBillingDay" class="form-control" placeholder="1-31" min="1" max="31">
                    </div>
                </div>

                <div class="form-group">
                    <label for="subNotes">Notities (optioneel)</label>
                    <textarea id="subNotes" class="form-control" rows="2" placeholder="bijv. gedeeld met pa en broer"></textarea>
                </div>

                <div class="form-group">
                    <label for="subBillingCycle">Facturatiecyclus</label>
                    <select id="subBillingCycle" class="form-control" required>
                        <option value="monthly">Maandelijks</option>
                        <option value="quarterly">Per kwartaal</option>
                        <option value="yearly">Jaarlijks</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subCategory">Categorie</label>
                    <select id="subCategory" class="form-control" required onchange="updateSubcategoryOptions('subSubcategory', this.value)">
                        <option value="">Selecteer categorie...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="subSubcategory">Subcategorie (optioneel)</label>
                    <select id="subSubcategory" class="form-control">
                        <option value="">Geen subcategorie</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subStartedAt">Startdatum (optioneel)</label>
                    <input type="date" id="subStartedAt" class="form-control">
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="subIsActive" checked style="width: auto; margin: 0;">
                        Actief
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeSubscriptionModal()" style="flex: 1;">Annuleren</button>
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
const allSubcategories = @json($subcategories);

let isEditing = false;
let editingId = null;

function updateSubcategoryOptions(selectId, categoryId) {
    const select = document.getElementById(selectId);
    const currentValue = select.value;
    const filtered = allSubcategories.filter(s => s.expense_category_id == categoryId);

    select.innerHTML = '<option value="">Geen subcategorie</option>';
    filtered.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name;
        if (s.id == currentValue) opt.selected = true;
        select.appendChild(opt);
    });
}

function openAddSubscriptionModal() {
    isEditing = false;
    editingId = null;

    document.getElementById('modalTitle').textContent = 'Abonnement toevoegen';
    document.getElementById('submitBtnText').textContent = 'Opslaan';
    document.getElementById('subscriptionId').value = '';
    document.getElementById('subName').value = '';
    document.getElementById('subAmount').value = '';
    document.getElementById('subOriginalAmount').value = '';
    document.getElementById('subBillingCycle').value = 'monthly';
    document.getElementById('subBillingDay').value = '';
    document.getElementById('subNotes').value = '';
    document.getElementById('subCategory').value = '';
    document.getElementById('subSubcategory').innerHTML = '<option value="">Geen subcategorie</option>';
    document.getElementById('subStartedAt').value = '';
    document.getElementById('subIsActive').checked = true;

    document.getElementById('subscriptionModal').classList.add('active');
    setTimeout(() => document.getElementById('subName').focus(), 100);
}

function openEditSubscriptionModal(subscription) {
    isEditing = true;
    editingId = subscription.id;

    document.getElementById('modalTitle').textContent = 'Abonnement bewerken';
    document.getElementById('submitBtnText').textContent = 'Bijwerken';
    document.getElementById('subscriptionId').value = subscription.id;
    document.getElementById('subName').value = subscription.name;
    document.getElementById('subAmount').value = subscription.amount;
    document.getElementById('subOriginalAmount').value = subscription.original_amount || '';
    document.getElementById('subBillingCycle').value = subscription.billing_cycle;
    document.getElementById('subBillingDay').value = subscription.billing_day || '';
    document.getElementById('subNotes').value = subscription.notes || '';
    document.getElementById('subCategory').value = subscription.expense_category_id;
    document.getElementById('subStartedAt').value = subscription.started_at ? subscription.started_at.split('T')[0] : '';
    document.getElementById('subIsActive').checked = subscription.is_active;

    updateSubcategoryOptions('subSubcategory', subscription.expense_category_id);
    document.getElementById('subSubcategory').value = subscription.expense_subcategory_id || '';

    document.getElementById('subscriptionModal').classList.add('active');
}

function closeSubscriptionModal() {
    document.getElementById('subscriptionModal').classList.remove('active');
    isEditing = false;
    editingId = null;
}

document.getElementById('subscriptionForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        name: document.getElementById('subName').value,
        amount: document.getElementById('subAmount').value,
        original_amount: document.getElementById('subOriginalAmount').value || null,
        billing_cycle: document.getElementById('subBillingCycle').value,
        billing_day: document.getElementById('subBillingDay').value || null,
        notes: document.getElementById('subNotes').value || null,
        expense_category_id: document.getElementById('subCategory').value,
        expense_subcategory_id: document.getElementById('subSubcategory').value || null,
        started_at: document.getElementById('subStartedAt').value || null,
        is_active: document.getElementById('subIsActive').checked,
    };

    const url = isEditing
        ? `{{ url('/expenses/subscriptions') }}/${editingId}`
        : '{{ route('expenses.subscriptions.store') }}';
    const method = isEditing ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message);
            closeSubscriptionModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Fout bij opslaan', 'error');
        }
    })
    .catch(() => showNotification('Fout bij opslaan', 'error'));
});

function deleteSubscription(id) {
    if (!confirm('Weet je zeker dat je dit abonnement wilt verwijderen?')) return;

    fetch(`{{ url('/expenses/subscriptions') }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Fout bij verwijderen', 'error');
        }
    })
    .catch(() => showNotification('Fout bij verwijderen', 'error'));
}

function showNotification(message, type = 'success') {
    const el = document.createElement('div');
    el.textContent = message;
    el.style.cssText = `position:fixed;top:2rem;right:2rem;padding:1rem 1.5rem;background:${type === 'success' ? '#10b981' : '#ef4444'};color:white;border-radius:0.5rem;box-shadow:0 10px 15px -3px rgba(0,0,0,.1);z-index:9999;animation:slideIn .3s ease-out`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeSubscriptionModal();
});

document.getElementById('subscriptionModal').addEventListener('click', e => {
    if (e.target.id === 'subscriptionModal') closeSubscriptionModal();
});
</script>

<style>
.subscriptions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}

.subscription-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.subscription-info {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    flex: 1;
}

.subscription-name {
    font-weight: 600;
    font-size: 1rem;
}

.subscription-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.subscription-billing {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.subscription-amount {
    font-weight: 600;
    color: #ef4444;
}

.subscription-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
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

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
@endsection
