<div class="journal-form-layout">
    <!-- Main content column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Title -->
        <div class="card">
            <div class="card-body">
                <div class="form-group" style="margin: 0;">
                    <input
                        type="text"
                        name="title"
                        class="form-control journal-title-input"
                        placeholder="Titel van je entry..."
                        value="{{ old('title', $entry?->title) }}"
                        required
                        autofocus
                    >
                    @error('title')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- WYSIWYG Editor -->
        <div class="card">
            <div class="card-body" style="padding: 0;">
                <!-- Toolbar -->
                <div class="tiptap-toolbar">
                    <button type="button" data-tiptap-action="bold" title="Vet"><i class="fas fa-bold"></i></button>
                    <button type="button" data-tiptap-action="italic" title="Cursief"><i class="fas fa-italic"></i></button>
                    <button type="button" data-tiptap-action="strike" title="Doorhalen"><i class="fas fa-strikethrough"></i></button>
                    <div class="tiptap-divider"></div>
                    <button type="button" data-tiptap-action="h2" title="Kop 2">H2</button>
                    <button type="button" data-tiptap-action="h3" title="Kop 3">H3</button>
                    <div class="tiptap-divider"></div>
                    <button type="button" data-tiptap-action="bullet" title="Opsomming"><i class="fas fa-list-ul"></i></button>
                    <button type="button" data-tiptap-action="ordered" title="Genummerde lijst"><i class="fas fa-list-ol"></i></button>
                    <button type="button" data-tiptap-action="blockquote" title="Citaat"><i class="fas fa-quote-right"></i></button>
                    <button type="button" data-tiptap-action="hr" title="Horizontale lijn"><i class="fas fa-minus"></i></button>
                    <div class="tiptap-divider"></div>
                    <button type="button" data-tiptap-action="undo" title="Ongedaan maken"><i class="fas fa-undo"></i></button>
                    <button type="button" data-tiptap-action="redo" title="Opnieuw"><i class="fas fa-redo"></i></button>
                    <div style="margin-left: auto; font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.25rem; padding-right: 0.25rem;">
                        <i class="fas fa-file-word"></i>
                        <span id="word-count">0</span> woorden
                    </div>
                </div>

                <!-- Editor surface -->
                <div id="tiptap-editor" class="tiptap-content"></div>

                <!-- Hidden input for form submission -->
                <textarea name="content" id="content-input" style="display: none;">{{ old('content', $entry?->content) }}</textarea>
            </div>
        </div>
    </div>

    <!-- Sidebar column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Actions -->
        <div class="card">
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> {{ $entry ? 'Bijwerken' : 'Opslaan' }}
                </button>
                <a href="{{ $entry ? route('journal.show', $entry) : route('journal.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">
                    Annuleren
                </a>
                @if($entry)
                    <form method="POST" action="{{ route('journal.destroy', $entry) }}" onsubmit="return confirm('Entry verwijderen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="width: 100%;">
                            <i class="fas fa-trash"></i> Verwijderen
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Date & Location -->
        <div class="card">
            <div class="card-header"><h3>Details</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Datum</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $entry?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    @error('date')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label><i class="fas fa-map-marker-alt" style="margin-right: 0.3rem;"></i> Locatie</label>
                    <input type="text" name="location" class="form-control" placeholder="bijv. Amsterdam, thuis..." value="{{ old('location', $entry?->location) }}">
                </div>
            </div>
        </div>

        <!-- Rating -->
        <div class="card">
            <div class="card-header"><h3>Dagcijfer</h3></div>
            <div class="card-body">
                <div class="journal-rating-picker">
                    @for($i = 1; $i <= 10; $i++)
                        <label class="rating-option">
                            <input type="radio" name="rating" value="{{ $i }}"
                                {{ old('rating', $entry?->rating) == $i ? 'checked' : '' }}>
                            <span class="rating-label">{{ $i }}</span>
                        </label>
                    @endfor
                </div>
                <button type="button" class="btn btn-secondary btn-sm" style="margin-top: 0.5rem; width: 100%;" onclick="document.querySelectorAll('[name=rating]').forEach(r => r.checked = false)">
                    Wis cijfer
                </button>
            </div>
        </div>

        <!-- Mood -->
        <div class="card">
            <div class="card-header"><h3>Humeur</h3></div>
            <div class="card-body">
                @if($moods->isEmpty())
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Nog geen moods aangemaakt. Ga naar <a href="{{ route('settings.moods') }}">Instellingen → Moods</a>.</p>
                @else
                    <div class="journal-mood-picker">
                        @foreach($moods as $mood)
                            <label class="mood-option" title="{{ $mood->name }}">
                                <input type="radio" name="mood_id" value="{{ $mood->id }}"
                                    {{ old('mood_id', $entry?->mood_id) == $mood->id ? 'checked' : '' }}>
                                <span class="mood-option-inner" style="--mood-color: {{ $mood->color }};">
                                    <i class="{{ $mood->icon }} mood-icon"></i>
                                    <span class="mood-name">{{ $mood->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" style="margin-top: 0.5rem; width: 100%;" onclick="document.querySelectorAll('[name=mood_id]').forEach(r => r.checked = false)">
                        Wis humeur
                    </button>
                @endif
            </div>
        </div>

        <!-- Tags -->
        <div class="card">
            <div class="card-header">
                <h3>Tags</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('new-tag-form').classList.toggle('hidden')">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <!-- New tag form -->
                <div id="new-tag-form" class="hidden" style="margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-secondary); border-radius: 0.5rem;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" id="new-tag-name" class="form-control form-control-sm" placeholder="Tag naam...">
                        <input type="color" id="new-tag-color" value="#6366f1" style="width: 40px; padding: 0.15rem; border-radius: 0.25rem; border: 1px solid var(--border-color); cursor: pointer; background: transparent;">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" style="width: 100%;" onclick="createTag()">
                        <i class="fas fa-plus"></i> Tag aanmaken
                    </button>
                </div>

                @if($tags->isEmpty())
                    <p style="font-size: 0.85rem; color: var(--text-muted);">Nog geen tags. Klik op + om een tag aan te maken.</p>
                @else
                    <div id="tags-list" style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                        @foreach($tags as $tag)
                            <label class="journal-tag-checkbox" title="{{ $tag->name }}">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tags', $entry?->tags->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                <span class="journal-tag-label" style="--tag-color: {{ $tag->color }};">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.journal-form-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .journal-form-layout {
        grid-template-columns: 1fr;
    }
}

.journal-title-input {
    font-size: 1.5rem;
    font-weight: 600;
    border: none;
    padding: 0.5rem 0;
    background: transparent;
    width: 100%;
}

.journal-title-input:focus {
    outline: none;
    box-shadow: none;
    border-bottom: 2px solid #6366f1;
}

/* Tiptap toolbar */
.tiptap-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.15rem;
    padding: 0.5rem;
    border-bottom: 1px solid var(--border-color);
}

.tiptap-toolbar button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.4rem;
    border: none;
    border-radius: 0.25rem;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    transition: background 0.1s, color 0.1s;
}

.tiptap-toolbar button:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.tiptap-toolbar button.is-active {
    background: #6366f120;
    color: #6366f1;
}

.tiptap-divider {
    width: 1px;
    height: 1.5rem;
    background: var(--border-color);
    margin: 0 0.25rem;
}

/* Tiptap editor area */
.tiptap-content {
    min-height: 400px;
    padding: 1.25rem;
    cursor: text;
}

.tiptap-content:focus-within {
    outline: none;
}

.tiptap-content .tiptap {
    min-height: 360px;
    outline: none;
    line-height: 1.7;
}

.tiptap-content .tiptap p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: var(--text-muted);
    pointer-events: none;
    height: 0;
}

.tiptap-content .tiptap h2 { font-size: 1.4rem; font-weight: 700; margin: 1.25rem 0 0.5rem; }
.tiptap-content .tiptap h3 { font-size: 1.15rem; font-weight: 600; margin: 1rem 0 0.4rem; }
.tiptap-content .tiptap ul, .tiptap-content .tiptap ol { padding-left: 1.5rem; margin: 0.5rem 0; }
.tiptap-content .tiptap li { margin: 0.25rem 0; }
.tiptap-content .tiptap blockquote { border-left: 3px solid #6366f1; padding-left: 1rem; color: var(--text-muted); margin: 1rem 0; font-style: italic; }
.tiptap-content .tiptap hr { border: none; border-top: 1px solid var(--border-color); margin: 1.5rem 0; }
.tiptap-content .tiptap strong { font-weight: 700; }
.tiptap-content .tiptap em { font-style: italic; }
.tiptap-content .tiptap s { text-decoration: line-through; }

/* Rating picker */
.journal-rating-picker {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.rating-option input { display: none; }

.rating-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    transition: all 0.1s;
}

.rating-option input:checked + .rating-label {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
}

.rating-label:hover {
    border-color: #6366f1;
    color: #6366f1;
}

/* Mood picker */
.journal-mood-picker {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.mood-option input { display: none; }

.mood-option-inner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.6rem;
    border-radius: 0.375rem;
    border: 1px solid var(--border-color);
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.1s;
}

.mood-option-inner:hover {
    border-color: var(--mood-color);
    background: color-mix(in srgb, var(--mood-color) 10%, transparent);
}

.mood-option input:checked + .mood-option-inner {
    border-color: var(--mood-color);
    background: color-mix(in srgb, var(--mood-color) 15%, transparent);
    color: var(--mood-color);
    font-weight: 600;
}

.mood-icon { font-size: 1rem; }

/* Tag checkboxes */
.journal-tag-checkbox input { display: none; }

.journal-tag-label {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid var(--border-color);
    transition: all 0.1s;
}

.journal-tag-label:hover {
    border-color: var(--tag-color);
    color: var(--tag-color);
}

.journal-tag-checkbox input:checked + .journal-tag-label {
    background: color-mix(in srgb, var(--tag-color) 15%, transparent);
    border-color: var(--tag-color);
    color: var(--tag-color);
    font-weight: 600;
}

.hidden { display: none !important; }
</style>

<script>
function createTag() {
    const name  = document.getElementById('new-tag-name').value.trim();
    const color = document.getElementById('new-tag-color').value;

    if (!name) { return; }

    fetch('{{ route('journal.tags.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name, color }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { return; }

        const tag = data.tag;
        const tagsList = document.getElementById('tags-list');

        if (!tagsList) {
            location.reload();
            return;
        }

        const label = document.createElement('label');
        label.className = 'journal-tag-checkbox';
        label.title = tag.name;
        label.innerHTML = `
            <input type="checkbox" name="tags[]" value="${tag.id}" checked>
            <span class="journal-tag-label" style="--tag-color: ${tag.color};">${tag.name}</span>
        `;
        tagsList.appendChild(label);

        document.getElementById('new-tag-name').value = '';
        document.getElementById('new-tag-form').classList.add('hidden');
    });
}
</script>
