@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1>Last.fm Import</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li>Last.fm</li>
                </ul>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Status -->
    <div class="stats-grid" style="margin-bottom: 1.5rem;">
        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e8183f 0%, #c0002c 100%);">
                        <i class="fas fa-record-vinyl"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ number_format($totalImported) }}</h3>
                        <p class="stat-label">Geïmporteerde scrobbles</p>
                        <span class="stat-change">Lokaal opgeslagen in Aura</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="stat-content">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">{{ $totalLastfm !== null ? number_format($totalLastfm) : '—' }}</h3>
                        <p class="stat-label">Totaal op Last.fm</p>
                        <span class="stat-change">
                            @if($totalLastfm !== null && $totalImported > 0)
                                {{ number_format(round(($totalImported / $totalLastfm) * 100)) }}% geïmporteerd
                            @elseif(!$isConfigured)
                                Niet geconfigureerd
                            @else
                                Nog niet geïmporteerd
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration check -->
    @if(!$isConfigured)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-right: 0.5rem;"></i>Configuratie vereist</h3>
            </div>
            <div class="card-body">
                <p>Voeg de volgende variabelen toe aan je <code>.env</code> bestand:</p>
                <pre style="background: var(--code-bg, #f3f4f6); padding: 1rem; border-radius: 6px; font-size: 0.875rem;">LASTFM_API_KEY=jouw_api_key
LASTFM_USERNAME=jouw_gebruikersnaam</pre>
                <p style="margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280;">
                    Haal een gratis API key op via <strong>last.fm/api/account/create</strong>.
                    Vergeet daarna niet <code>php artisan config:clear</code> te draaien.
                </p>
            </div>
        </div>
    @endif

    <!-- Import controls -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3><i class="fas fa-download" style="margin-right: 0.5rem;"></i>Import beheren</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap;">
                <form method="POST" action="{{ route('lastfm.import') }}">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-primary"
                        @if(!$isConfigured || ($importStatus['running'] ?? false)) disabled @endif
                    >
                        <i class="fas fa-play"></i>
                        @if($importStatus['running'] ?? false)
                            Import bezig...
                        @elseif($totalImported > 0)
                            Opnieuw importeren
                        @else
                            Start import
                        @endif
                    </button>
                </form>

                @if($totalImported > 0)
                    <form method="POST" action="{{ route('lastfm.clear') }}"
                          onsubmit="return confirm('Weet je zeker dat je alle {{ number_format($totalImported) }} geïmporteerde scrobbles wilt verwijderen? Dit is niet terug te draaien.')">
                        @csrf
                        <button type="submit" class="btn btn-danger" @if($importStatus['running'] ?? false) disabled @endif>
                            <i class="fas fa-trash"></i> Alles verwijderen
                        </button>
                    </form>
                @endif
            </div>

            <p style="margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280;">
                De import loopt als achtergrondtaak en haalt pagina voor pagina je volledige scrobble-geschiedenis op.
                Bij ~100.000 scrobbles duurt dit ongeveer 5–10 minuten.
                Duplicaten worden automatisch overgeslagen.
            </p>
        </div>
    </div>

    <!-- Live progress -->
    <div class="card" id="progress-card" style="{{ ($importStatus['running'] ?? false) ? '' : 'display: none;' }}">
        <div class="card-header">
            <h3><i class="fas fa-spinner fa-spin" style="margin-right: 0.5rem;"></i>Voortgang</h3>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.375rem; font-size: 0.875rem;">
                    <span id="progress-label">Pagina <span id="current-page">—</span> van <span id="total-pages">—</span></span>
                    <span id="progress-pct">—%</span>
                </div>
                <div style="height: 8px; background: var(--border-color, #e5e7eb); border-radius: 4px; overflow: hidden;">
                    <div id="progress-bar" style="height: 100%; background: linear-gradient(90deg, #e8183f, #c0002c); border-radius: 4px; width: 0%; transition: width 0.5s;"></div>
                </div>
            </div>
            <div style="display: flex; gap: 2rem; font-size: 0.875rem;">
                <span><strong id="imported-count">0</strong> geïmporteerd</span>
                <span><strong id="skipped-count">0</strong> overgeslagen</span>
            </div>
            <div id="error-message" style="display: none; margin-top: 0.75rem; color: #ef4444; font-size: 0.875rem;"></div>
        </div>
    </div>
</div>

<script>
    function pollImportStatus() {
        fetch('{{ route('lastfm.import.status') }}')
            .then(r => r.json())
            .then(data => {
                const card = document.getElementById('progress-card');

                if (data.running || data.imported > 0) {
                    card.style.display = '';
                }

                document.getElementById('current-page').textContent = data.page ?? '—';
                document.getElementById('total-pages').textContent = data.total_pages ?? '—';
                document.getElementById('imported-count').textContent = (data.imported ?? 0).toLocaleString();
                document.getElementById('skipped-count').textContent = (data.skipped ?? 0).toLocaleString();

                if (data.total_pages > 0) {
                    const pct = Math.round((data.page / data.total_pages) * 100);
                    document.getElementById('progress-bar').style.width = pct + '%';
                    document.getElementById('progress-pct').textContent = pct + '%';
                }

                if (data.error) {
                    const el = document.getElementById('error-message');
                    el.style.display = '';
                    el.textContent = 'Fout: ' + data.error;
                }

                if (data.running) {
                    setTimeout(pollImportStatus, 2000);
                } else if (!data.running && data.imported > 0 && data.total_pages !== null) {
                    // Reload to update the imported count stat
                    setTimeout(() => window.location.reload(), 1500);
                }
            });
    }

    @if($importStatus['running'] ?? false)
        pollImportStatus();
    @endif
</script>
@endsection
