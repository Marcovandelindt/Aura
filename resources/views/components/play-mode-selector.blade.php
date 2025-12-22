@props(['selected' => null])

@php
    use App\Enums\PlayMode;
    $playModes = PlayMode::cases();
@endphp

<div class="form-group">
    <label for="play_mode" class="form-label">Play Mode</label>
    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        @foreach($playModes as $mode)
            @php
                $isSelected = old('play_mode', $selected?->value) === $mode->value;
            @endphp
            <label class="play-mode-option" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: {{ $isSelected ? $mode->color() : 'var(--badge-bg, #f3f4f6)' }}; color: {{ $isSelected ? 'white' : 'var(--text-primary, inherit)' }}; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
                <input type="radio" name="play_mode" value="{{ $mode->value }}" {{ $isSelected ? 'checked' : '' }} style="display: none;" onchange="updatePlayModeSelection(this, '{{ $mode->color() }}')">
                <i class="{{ $mode->icon() }}"></i>
                {{ $mode->label() }}
            </label>
        @endforeach
        {{-- Clear option --}}
        <label class="play-mode-option" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: {{ old('play_mode', $selected?->value) === null ? 'var(--text-muted, #6b7280)' : 'var(--badge-bg, #f3f4f6)' }}; color: {{ old('play_mode', $selected?->value) === null ? 'white' : 'var(--text-primary, inherit)' }}; border-radius: 6px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
            <input type="radio" name="play_mode" value="" {{ old('play_mode', $selected?->value) === null ? 'checked' : '' }} style="display: none;" onchange="updatePlayModeSelection(this, 'var(--text-muted, #6b7280)')">
            <i class="fas fa-minus"></i>
            Not set
        </label>
    </div>
</div>

@once
@push('scripts')
<script>
function updatePlayModeSelection(radio, color) {
    // Reset all options
    document.querySelectorAll('.play-mode-option').forEach(function(label) {
        label.style.background = 'var(--badge-bg, #f3f4f6)';
        label.style.color = 'var(--text-primary, inherit)';
    });

    // Highlight selected option
    if (radio.checked) {
        radio.parentElement.style.background = color;
        radio.parentElement.style.color = 'white';
    }
}
</script>
@endpush
@endonce
