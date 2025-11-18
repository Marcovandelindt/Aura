@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Mood Settings</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Settings</li>
                    <li>Moods</li>
                </ul>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="openAddMoodModal()">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i>
                    Add Mood
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['total'] }}</h3>
                        <p class="stat-label">Total Moods</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['active'] }}</h3>
                        <p class="stat-label">Active</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['inactive'] }}</h3>
                        <p class="stat-label">Inactive</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['total_usage'] }}</h3>
                        <p class="stat-label">Total Tags</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Moods List -->
    <div class="card">
        <div class="card-header">
            <h3>All Moods</h3>
        </div>
        <div class="card-body">
            @if($moods->count() > 0)
                <div class="moods-settings-list">
                    @foreach($moods as $mood)
                        <div class="mood-settings-item {{ !$mood->is_active ? 'inactive' : '' }}">
                            <div class="mood-settings-preview">
                                <div class="mood-settings-icon" style="background-color: {{ $mood->color }}20; color: {{ $mood->color }};">
                                    <i class="{{ $mood->icon }}"></i>
                                </div>
                                <div class="mood-settings-color" style="background-color: {{ $mood->color }};"></div>
                            </div>
                            <div class="mood-settings-info">
                                <div class="mood-settings-name">
                                    {{ $mood->name }}
                                    @if(!$mood->is_active)
                                        <span class="badge-inactive">Inactive</span>
                                    @endif
                                </div>
                                <div class="mood-settings-meta">
                                    <span><i class="{{ $mood->icon }}"></i> {{ $mood->icon }}</span>
                                    <span><i class="fas fa-palette"></i> {{ $mood->color }}</span>
                                    <span><i class="fas fa-tag"></i> {{ $mood->usage_count }} tracks</span>
                                </div>
                                @if($mood->description)
                                    <div class="mood-settings-description">{{ $mood->description }}</div>
                                @endif
                            </div>
                            <div class="mood-settings-actions">
                                <form action="{{ route('settings.moods.toggle', $mood) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-icon" title="{{ $mood->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $mood->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn-icon" onclick="editMood({{ json_encode($mood) }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('settings.moods.destroy', $mood) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this mood? This will remove it from all tracks.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-heart" style="font-size: 4rem; margin-bottom: 1rem; color: #ccc;"></i>
                    <h3>No moods yet</h3>
                    <p>Create your first mood to start tagging your music!</p>
                    <button type="button" class="btn btn-primary" onclick="openAddMoodModal()">Add Mood</button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add/Edit Mood Modal -->
<div id="moodModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Mood</h3>
            <button type="button" class="modal-close" onclick="closeMoodModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="moodForm" method="POST" action="{{ route('settings.moods.store') }}">
                @csrf
                <div id="methodField"></div>

                <div class="form-group">
                    <label for="name">Name <span style="color: red;">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required maxlength="50" placeholder="e.g. Energetic">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="icon">Icon Class <span style="color: red;">*</span></label>
                        <input type="text" id="icon" name="icon" class="form-control" required maxlength="50" placeholder="e.g. fas fa-bolt">
                        <small style="color: #666;">Use FontAwesome classes like "fas fa-heart"</small>
                    </div>

                    <div class="form-group">
                        <label for="color">Color <span style="color: red;">*</span></label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="color" id="colorPicker" value="#6366f1" onchange="document.getElementById('color').value = this.value" style="width: 50px; height: 38px; border: none; padding: 0; cursor: pointer;">
                            <input type="text" id="color" name="color" class="form-control" required pattern="^#[0-9A-Fa-f]{6}$" placeholder="#6366f1" onchange="document.getElementById('colorPicker').value = this.value">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="2" maxlength="255" placeholder="Optional description"></textarea>
                </div>

                <div class="form-group" id="activeGroup" style="display: none;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                        Active
                    </label>
                </div>

                <!-- Preview -->
                <div class="form-group">
                    <label>Preview</label>
                    <div id="moodPreview" class="mood-preview">
                        <div class="mood-preview-icon" style="background-color: #6366f120; color: #6366f1;">
                            <i class="fas fa-heart"></i>
                        </div>
                        <span class="mood-preview-name">Mood Name</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeMoodModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Mood</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddMoodModal() {
    document.getElementById('modalTitle').textContent = 'Add New Mood';
    document.getElementById('moodForm').action = '{{ route("settings.moods.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('activeGroup').style.display = 'none';

    // Clear form
    document.getElementById('name').value = '';
    document.getElementById('icon').value = '';
    document.getElementById('color').value = '#6366f1';
    document.getElementById('colorPicker').value = '#6366f1';
    document.getElementById('description').value = '';

    updatePreview();
    document.getElementById('moodModal').style.display = 'flex';
}

function editMood(mood) {
    document.getElementById('modalTitle').textContent = 'Edit Mood';
    document.getElementById('moodForm').action = `/settings/moods/${mood.id}`;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('activeGroup').style.display = 'block';

    // Fill form
    document.getElementById('name').value = mood.name;
    document.getElementById('icon').value = mood.icon;
    document.getElementById('color').value = mood.color;
    document.getElementById('colorPicker').value = mood.color;
    document.getElementById('description').value = mood.description || '';
    document.getElementById('is_active').checked = mood.is_active;

    updatePreview();
    document.getElementById('moodModal').style.display = 'flex';
}

function closeMoodModal() {
    document.getElementById('moodModal').style.display = 'none';
}

function updatePreview() {
    const name = document.getElementById('name').value || 'Mood Name';
    const icon = document.getElementById('icon').value || 'fas fa-heart';
    const color = document.getElementById('color').value || '#6366f1';

    const previewIcon = document.querySelector('.mood-preview-icon');
    const previewName = document.querySelector('.mood-preview-name');

    previewIcon.style.backgroundColor = color + '20';
    previewIcon.style.color = color;
    previewIcon.innerHTML = `<i class="${icon}"></i>`;
    previewName.textContent = name;
}

// Update preview on input changes
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('icon').addEventListener('input', updatePreview);
document.getElementById('color').addEventListener('input', updatePreview);
document.getElementById('colorPicker').addEventListener('input', function() {
    document.getElementById('color').value = this.value;
    updatePreview();
});

// Close modal when clicking outside
document.getElementById('moodModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMoodModal();
    }
});
</script>
@endsection
