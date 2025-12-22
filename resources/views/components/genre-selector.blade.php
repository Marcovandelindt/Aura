@props(['genres', 'selectedGenres' => [], 'accentColor' => '#6366f1'])

<div class="genre-selector" data-accent-color="{{ $accentColor }}">
    <label class="form-label">Genres</label>

    {{-- Search input --}}
    <div style="margin-bottom: 0.75rem;">
        <input type="text"
               class="form-control genre-search"
               placeholder="Search genres..."
               style="max-width: 300px;">
    </div>

    {{-- Genre tags --}}
    <div class="genre-tags" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
        @foreach($genres as $genre)
            @php
                $isSelected = in_array($genre->id, old('genres', $selectedGenres));
            @endphp
            <label class="genre-tag {{ $isSelected ? 'selected' : '' }}"
                   data-genre-name="{{ strtolower($genre->name) }}"
                   style="display: flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; background: {{ $isSelected ? $accentColor : 'var(--badge-bg, #f3f4f6)' }}; color: {{ $isSelected ? 'white' : 'var(--text-primary, inherit)' }}; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
                <input type="checkbox"
                       name="genres[]"
                       value="{{ $genre->id }}"
                       {{ $isSelected ? 'checked' : '' }}
                       style="display: none;">
                {{ $genre->name }}
            </label>
        @endforeach
    </div>

    {{-- Add new genre --}}
    <div class="add-genre-section">
        <button type="button" class="btn btn-sm btn-secondary toggle-add-genre" style="font-size: 0.8125rem;">
            <i class="fas fa-plus" style="margin-right: 4px;"></i> Add new genre
        </button>

        <div class="add-genre-form" style="display: none; margin-top: 0.75rem;">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="text"
                       class="form-control new-genre-input"
                       placeholder="Enter genre name..."
                       style="max-width: 200px; font-size: 0.875rem;">
                <button type="button" class="btn btn-sm btn-primary save-genre">
                    <i class="fas fa-check"></i>
                </button>
                <button type="button" class="btn btn-sm btn-secondary cancel-add-genre">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="genre-error" style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: none;"></div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.genre-selector').forEach(function(selector) {
        const accentColor = selector.dataset.accentColor;
        const searchInput = selector.querySelector('.genre-search');
        const genreTags = selector.querySelectorAll('.genre-tag');
        const toggleBtn = selector.querySelector('.toggle-add-genre');
        const addForm = selector.querySelector('.add-genre-form');
        const newGenreInput = selector.querySelector('.new-genre-input');
        const saveBtn = selector.querySelector('.save-genre');
        const cancelBtn = selector.querySelector('.cancel-add-genre');
        const errorDiv = selector.querySelector('.genre-error');
        const tagsContainer = selector.querySelector('.genre-tags');

        // Toggle genre selection
        genreTags.forEach(function(tag) {
            tag.addEventListener('click', function() {
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;

                if (checkbox.checked) {
                    this.classList.add('selected');
                    this.style.background = accentColor;
                    this.style.color = 'white';
                } else {
                    this.classList.remove('selected');
                    this.style.background = 'var(--badge-bg, #f3f4f6)';
                    this.style.color = 'var(--text-primary, inherit)';
                }
            });
        });

        // Search/filter genres
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            genreTags.forEach(function(tag) {
                const genreName = tag.dataset.genreName;
                if (genreName.includes(searchTerm)) {
                    tag.style.display = 'flex';
                } else {
                    tag.style.display = 'none';
                }
            });
        });

        // Toggle add genre form
        toggleBtn.addEventListener('click', function() {
            addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
            if (addForm.style.display === 'block') {
                newGenreInput.focus();
            }
        });

        cancelBtn.addEventListener('click', function() {
            addForm.style.display = 'none';
            newGenreInput.value = '';
            errorDiv.style.display = 'none';
        });

        // Save new genre
        saveBtn.addEventListener('click', function() {
            const name = newGenreInput.value.trim();
            if (!name) {
                errorDiv.textContent = 'Please enter a genre name';
                errorDiv.style.display = 'block';
                return;
            }

            saveBtn.disabled = true;
            errorDiv.style.display = 'none';

            fetch('{{ route("genres.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Create new tag
                    const newTag = document.createElement('label');
                    newTag.className = 'genre-tag selected';
                    newTag.dataset.genreName = data.genre.name.toLowerCase();
                    newTag.style.cssText = `display: flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; background: ${accentColor}; color: white; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;`;
                    newTag.innerHTML = `<input type="checkbox" name="genres[]" value="${data.genre.id}" checked style="display: none;"> ${data.genre.name}`;

                    // Add click handler
                    newTag.addEventListener('click', function() {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        checkbox.checked = !checkbox.checked;
                        if (checkbox.checked) {
                            this.classList.add('selected');
                            this.style.background = accentColor;
                            this.style.color = 'white';
                        } else {
                            this.classList.remove('selected');
                            this.style.background = 'var(--badge-bg, #f3f4f6)';
                            this.style.color = 'var(--text-primary, inherit)';
                        }
                    });

                    tagsContainer.appendChild(newTag);
                    newGenreInput.value = '';
                    addForm.style.display = 'none';
                } else {
                    errorDiv.textContent = data.message || 'Failed to add genre';
                    errorDiv.style.display = 'block';
                }
            })
            .catch(error => {
                if (error.response && error.response.status === 422) {
                    error.response.json().then(data => {
                        errorDiv.textContent = data.errors?.name?.[0] || 'Validation failed';
                        errorDiv.style.display = 'block';
                    });
                } else {
                    errorDiv.textContent = 'Genre already exists or invalid name';
                    errorDiv.style.display = 'block';
                }
            })
            .finally(() => {
                saveBtn.disabled = false;
            });
        });

        // Enter key to save
        newGenreInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveBtn.click();
            }
        });
    });
});
</script>
@endpush
@endonce
