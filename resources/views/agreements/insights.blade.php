@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Inzichten</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('agreements.index') }}">Afspraken & Doelen</a></li>
                    <li>Inzichten</li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    {{-- Add new insight --}}
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form method="POST" action="{{ route('insights.store') }}">
                @csrf
                <div class="form-group" style="margin-bottom: 1rem;">
                    <textarea
                        name="body"
                        class="form-control"
                        rows="3"
                        placeholder="Schrijf een inzicht op..."
                        style="resize: vertical;"
                        required
                    ></textarea>
                    @error('body')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Toevoegen
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Insights list --}}
    @if($insights->isEmpty())
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fas fa-seedling" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;"></i>
            <p style="font-size: 0.9rem;">Nog geen inzichten. Schrijf het eerste op.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($insights as $insight)
                <div class="insight-entry">
                    <div class="insight-body">{{ $insight->body }}</div>
                    <div class="insight-footer">
                        <span class="insight-date">{{ $insight->created_at->locale('nl')->translatedFormat('d F Y') }}</span>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <form method="POST" action="{{ route('insights.resend', $insight) }}">
                                @csrf
                                <button type="submit" class="insight-action" title="Stuur naar Telegram">
                                    <i class="fab fa-telegram"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('insights.destroy', $insight) }}" onsubmit="return confirm('Verwijderen?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="insight-action insight-delete" title="Verwijderen">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.insight-entry {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.1rem 1.25rem;
}

.insight-body {
    font-size: 0.95rem;
    line-height: 1.6;
    white-space: pre-wrap;
    margin-bottom: 0.6rem;
}

.insight-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.insight-date {
    font-size: 0.75rem;
    color: var(--text-muted);
    opacity: 0.7;
}

.insight-action {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    opacity: 0.4;
    font-size: 0.75rem;
    padding: 0.2rem 0.4rem;
    transition: opacity 0.15s, color 0.15s;
}

.insight-action:hover {
    opacity: 0.8;
}

.insight-delete:hover {
    color: #ef4444;
}
</style>
@endsection
