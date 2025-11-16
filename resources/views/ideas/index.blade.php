@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Development Ideas</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Ideas</li>
                </ul>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="openAddIdeaModal()">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i>
                    Add Idea
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

    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['total'] }}</h3>
                        <p class="stat-label">Total Ideas</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['active'] }}</h3>
                        <p class="stat-label">Active Ideas</p>
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
                        <h3 class="stat-value">{{ $stats['completed'] }}</h3>
                        <p class="stat-label">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $stats['high_priority'] }}</h3>
                        <p class="stat-label">High Priority</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('ideas.index') }}" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div>
                    <select name="filter" class="form-control" onchange="this.form.submit()">
                        <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Ideas</option>
                        <option value="active" {{ $filter == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ $filter == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div>
                    <select name="priority" class="form-control" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        <option value="high" {{ $priority == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ $priority == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ $priority == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="feature" {{ $category == 'feature' ? 'selected' : '' }}>Feature</option>
                        <option value="improvement" {{ $category == 'improvement' ? 'selected' : '' }}>Improvement</option>
                        <option value="bug" {{ $category == 'bug' ? 'selected' : '' }}>Bug</option>
                        <option value="design" {{ $category == 'design' ? 'selected' : '' }}>Design</option>
                        <option value="other" {{ $category == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                @if($filter != 'all' || $priority || $category)
                    <a href="{{ route('ideas.index') }}" class="btn btn-secondary">Clear Filters</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Ideas List -->
    <div class="card">
        <div class="card-body">
            @if($ideas->count() > 0)
                <div class="ideas-list">
                    @foreach($ideas as $idea)
                        <div class="idea-item {{ $idea->is_completed ? 'completed' : '' }}" data-idea-id="{{ $idea->id }}">
                            <div class="idea-header">
                                <div class="idea-check">
                                    <form action="{{ route('ideas.toggle', $idea) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="idea-checkbox {{ $idea->is_completed ? 'checked' : '' }}">
                                            @if($idea->is_completed)
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                                <div class="idea-content">
                                    <div class="idea-title-row">
                                        <h4 class="idea-title">{{ $idea->title }}</h4>
                                        <div class="idea-meta">
                                            <span class="priority-badge priority-{{ $idea->priority }}">{{ ucfirst($idea->priority) }}</span>
                                            <span class="category-badge category-{{ $idea->category }}">{{ ucfirst($idea->category) }}</span>
                                        </div>
                                    </div>
                                    @if($idea->description)
                                        <p class="idea-description">{{ $idea->description }}</p>
                                    @endif
                                    <div class="idea-footer">
                                        <small class="idea-date">
                                            Created {{ $idea->created_at->diffForHumans() }}
                                            @if($idea->is_completed && $idea->completed_at)
                                                • Completed {{ $idea->completed_at->diffForHumans() }}
                                            @endif
                                        </small>
                                        <div class="idea-actions">
                                            <button type="button" class="btn-icon" onclick="editIdea({{ $idea->id }}, '{{ $idea->title }}', '{{ $idea->description }}', '{{ $idea->priority }}', '{{ $idea->category }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('ideas.destroy', $idea) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this idea?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-lightbulb" style="font-size: 4rem; margin-bottom: 1rem; color: #ccc;"></i>
                    <h3>No ideas found</h3>
                    <p>Start by adding your first development idea!</p>
                    <button type="button" class="btn btn-primary" onclick="openAddIdeaModal()">Add Idea</button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add/Edit Idea Modal -->
<div id="ideaModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Idea</h3>
            <button type="button" class="modal-close" onclick="closeIdeaModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="ideaForm" method="POST" action="{{ route('ideas.store') }}">
                @csrf
                <div class="form-group">
                    <label for="title">Title <span style="color: red;">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" class="form-control" required>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="feature">Feature</option>
                            <option value="improvement">Improvement</option>
                            <option value="bug">Bug</option>
                            <option value="design">Design</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeIdeaModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Idea</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddIdeaModal() {
    document.getElementById('modalTitle').textContent = 'Add New Idea';
    document.getElementById('ideaForm').action = '{{ route("ideas.store") }}';
    document.getElementById('ideaForm').method = 'POST';
    
    // Clear form
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('priority').value = 'medium';
    document.getElementById('category').value = 'feature';
    
    // Remove method field if exists
    const methodField = document.querySelector('input[name="_method"]');
    if (methodField) {
        methodField.remove();
    }
    
    document.getElementById('ideaModal').style.display = 'flex';
}

function editIdea(id, title, description, priority, category) {
    document.getElementById('modalTitle').textContent = 'Edit Idea';
    document.getElementById('ideaForm').action = `/ideas/${id}`;
    
    // Add PUT method
    if (!document.querySelector('input[name="_method"]')) {
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';
        document.getElementById('ideaForm').appendChild(methodField);
    }
    
    // Fill form
    document.getElementById('title').value = title;
    document.getElementById('description').value = description;
    document.getElementById('priority').value = priority;
    document.getElementById('category').value = category;
    
    document.getElementById('ideaModal').style.display = 'flex';
}

function closeIdeaModal() {
    document.getElementById('ideaModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('ideaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeIdeaModal();
    }
});
</script>
@endsection