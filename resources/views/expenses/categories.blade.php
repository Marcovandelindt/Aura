@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Uitgave Categorieën</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('expenses.index') }}">Uitgaven</a></li>
                    <li>Categorieën</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Terug
                </a>
                <button onclick="openAddCategoryModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Categorie toevoegen
                </button>
            </div>
        </div>
    </div>

    @if($categories->isEmpty())
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <i class="fas fa-tags" style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.5rem;">Nog geen categorieën</h3>
                <p style="color: #6b7280;">Maak categorieën aan om je uitgaven te organiseren.</p>
                <button onclick="openAddCategoryModal()" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Eerste categorie toevoegen
                </button>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="categories-grid">
                    @foreach($categories as $category)
                        <div class="category-card">
                            <div class="category-header" style="border-left: 4px solid {{ $category->color }};">
                                <div class="category-icon" style="background-color: {{ $category->color }}20; color: {{ $category->color }};">
                                    <i class="fas {{ $category->icon }}"></i>
                                </div>
                                <div class="category-info">
                                    <h4>{{ $category->name }}</h4>
                                    <div class="category-stats">
                                        <span>{{ $category->expenses_count }} uitgaven</span>
                                        @if($category->expenses_sum_amount)
                                            <span class="category-total">€{{ number_format($category->expenses_sum_amount, 2, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="category-actions">
                                <button class="btn btn-sm btn-secondary" onclick="openEditCategoryModal({{ json_encode($category) }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory({{ $category->id }}, {{ $category->expenses_count }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Add/Edit Category Modal -->
<div class="mood-popup-overlay" id="categoryModal">
    <div class="mood-popup">
        <div class="mood-popup-header">
            <h3 id="modalTitle">Categorie toevoegen</h3>
            <button class="mood-popup-close" onclick="closeCategoryModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mood-popup-content">
            <form id="categoryForm">
                <input type="hidden" id="categoryId" value="">

                <div class="form-group">
                    <label for="categoryName">Naam</label>
                    <input type="text" id="categoryName" class="form-control" placeholder="Naam van de categorie" required>
                </div>

                <div class="form-group">
                    <label for="categoryColor">Kleur</label>
                    <input type="color" id="categoryColor" class="form-control" value="#6366f1" style="height: 40px; padding: 4px;">
                </div>

                <div class="form-group">
                    <label>Icoon</label>
                    <div class="icon-picker" id="iconPicker">
                        @foreach(['fa-tag', 'fa-shopping-cart', 'fa-utensils', 'fa-car', 'fa-home', 'fa-bolt', 'fa-phone', 'fa-plane', 'fa-gift', 'fa-heart', 'fa-gamepad', 'fa-film', 'fa-music', 'fa-book', 'fa-graduation-cap', 'fa-briefcase', 'fa-medkit', 'fa-tshirt', 'fa-coffee', 'fa-beer'] as $icon)
                            <button type="button" class="icon-option {{ $icon === 'fa-tag' ? 'selected' : '' }}" data-icon="{{ $icon }}">
                                <i class="fas {{ $icon }}"></i>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" id="categoryIcon" value="fa-tag">
                </div>

                <div class="form-group">
                    <label>Voorbeeld</label>
                    <div id="categoryPreview" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: var(--card-bg); border-radius: 0.5rem;">
                        <span id="previewBadge" class="expense-category-badge" style="background-color: #6366f120; color: #6366f1; border: 1px solid #6366f140;">
                            <i class="fas fa-tag" id="previewIcon"></i>
                            <span id="previewName">Categorie</span>
                        </span>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()" style="flex: 1;">Annuleren</button>
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

// Icon picker
document.querySelectorAll('.icon-option').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.icon-option').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('categoryIcon').value = btn.dataset.icon;
        updatePreview();
    });
});

// Color and name change listeners
document.getElementById('categoryColor').addEventListener('input', updatePreview);
document.getElementById('categoryName').addEventListener('input', updatePreview);

function updatePreview() {
    const name = document.getElementById('categoryName').value || 'Categorie';
    const color = document.getElementById('categoryColor').value;
    const icon = document.getElementById('categoryIcon').value;

    const badge = document.getElementById('previewBadge');
    badge.style.backgroundColor = color + '20';
    badge.style.color = color;
    badge.style.borderColor = color + '40';

    document.getElementById('previewIcon').className = 'fas ' + icon;
    document.getElementById('previewName').textContent = name;
}

function openAddCategoryModal() {
    isEditing = false;
    editingId = null;

    document.getElementById('modalTitle').textContent = 'Categorie toevoegen';
    document.getElementById('submitBtnText').textContent = 'Opslaan';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryColor').value = '#6366f1';
    document.getElementById('categoryIcon').value = 'fa-tag';

    document.querySelectorAll('.icon-option').forEach(b => b.classList.remove('selected'));
    document.querySelector('.icon-option[data-icon="fa-tag"]').classList.add('selected');

    updatePreview();
    document.getElementById('categoryModal').classList.add('active');
    setTimeout(() => document.getElementById('categoryName').focus(), 100);
}

function openEditCategoryModal(category) {
    isEditing = true;
    editingId = category.id;

    document.getElementById('modalTitle').textContent = 'Categorie bewerken';
    document.getElementById('submitBtnText').textContent = 'Bijwerken';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryColor').value = category.color;
    document.getElementById('categoryIcon').value = category.icon;

    document.querySelectorAll('.icon-option').forEach(b => b.classList.remove('selected'));
    const iconBtn = document.querySelector(`.icon-option[data-icon="${category.icon}"]`);
    if (iconBtn) iconBtn.classList.add('selected');

    updatePreview();
    document.getElementById('categoryModal').classList.add('active');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.remove('active');
    isEditing = false;
    editingId = null;
}

document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        name: document.getElementById('categoryName').value,
        color: document.getElementById('categoryColor').value,
        icon: document.getElementById('categoryIcon').value,
    };

    const url = isEditing ? `{{ url('/expenses/categories') }}/${editingId}` : '{{ route('expenses.categories.store') }}';
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
            closeCategoryModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message || 'Fout bij opslaan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Fout bij opslaan', 'error');
    });
});

function deleteCategory(id, expenseCount) {
    if (expenseCount > 0) {
        showNotification('Categorie kan niet worden verwijderd omdat er nog uitgaven aan gekoppeld zijn', 'error');
        return;
    }

    if (!confirm('Weet je zeker dat je deze categorie wilt verwijderen?')) {
        return;
    }

    fetch(`{{ url('/expenses/categories') }}/${id}`, {
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
    const modal = document.getElementById('categoryModal');
    const isModalOpen = modal.classList.contains('active');
    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);

    if (e.key === 'Escape' && isModalOpen) {
        closeCategoryModal();
    }

    if (e.key === 'o' && !isModalOpen && !isTyping) {
        e.preventDefault();
        openAddCategoryModal();
    }
});

document.getElementById('categoryModal').addEventListener('click', (e) => {
    if (e.target.id === 'categoryModal') {
        closeCategoryModal();
    }
});
</script>

<style>
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.category-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-left: 0.75rem;
}

.category-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
}

.category-info h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
}

.category-stats {
    font-size: 0.75rem;
    color: var(--text-secondary);
    display: flex;
    gap: 0.75rem;
}

.category-total {
    font-weight: 600;
    color: #ef4444;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
}

.icon-picker {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 0.5rem;
}

.icon-option {
    width: 36px;
    height: 36px;
    border: 1px solid var(--border-color);
    border-radius: 0.375rem;
    background: var(--card-bg);
    color: var(--text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.icon-option:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.icon-option.selected {
    border-color: var(--primary-color);
    background: var(--primary-color);
    color: white;
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
