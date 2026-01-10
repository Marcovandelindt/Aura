@props(['platform', 'platformName', 'accentColor' => '#6366f1'])

<div id="recommendationModal" class="recommendation-overlay">
    <div class="recommendation-modal">
        <div class="recommendation-header">
            <h2><i class="fas fa-magic" style="margin-right: 8px;"></i>Game Aanbeveling</h2>
            <button type="button" class="recommendation-close" onclick="closeRecommendation()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="recommendation-content">
            <!-- Loading State -->
            <div id="recommendationLoading" class="recommendation-loading">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: {{ $accentColor }};"></i>
                <p>Even kijken wat je zou moeten spelen...</p>
            </div>

            <!-- No Games State -->
            <div id="recommendationEmpty" class="recommendation-empty" style="display: none;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981;"></i>
                <h3>Geen aanbevelingen</h3>
                <p>Er zijn geen games gevonden om aan te bevelen. Je hebt alles al gespeeld!</p>
            </div>

            <!-- Game Card -->
            <div id="recommendationGame" class="recommendation-game" style="display: none;">
                <div class="recommendation-game-header">
                    <img id="recommendationImage" src="" alt="" class="recommendation-game-image">
                    <div class="recommendation-game-info">
                        <h3 id="recommendationName"></h3>
                        <div id="recommendationPlatformBadge" class="recommendation-badge" style="background: {{ $accentColor }};">
                            {{ $platformName }}
                        </div>
                    </div>
                </div>

                <div class="recommendation-stats">
                    <div class="recommendation-stat">
                        <i class="fas fa-euro-sign"></i>
                        <div>
                            <span class="recommendation-stat-value" id="recommendationPrice">-</span>
                            <span class="recommendation-stat-label">Betaald</span>
                        </div>
                    </div>
                    <div class="recommendation-stat">
                        <i class="fas fa-clock"></i>
                        <div>
                            <span class="recommendation-stat-value" id="recommendationPlaytime">-</span>
                            <span class="recommendation-stat-label">Gespeeld</span>
                        </div>
                    </div>
                    <div class="recommendation-stat">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <span class="recommendation-stat-value" id="recommendationLastPlayed">-</span>
                            <span class="recommendation-stat-label">Laatst gespeeld</span>
                        </div>
                    </div>
                </div>

                <div id="recommendationReason" class="recommendation-reason">
                    <i class="fas fa-lightbulb"></i>
                    <span id="recommendationReasonText"></span>
                </div>

                <div class="recommendation-actions">
                    <button type="button" class="btn btn-primary recommendation-accept" onclick="acceptRecommendation()">
                        <i class="fas fa-play"></i> Ga ik spelen
                    </button>
                    <button type="button" class="btn btn-secondary recommendation-reject" onclick="rejectRecommendation()">
                        <i class="fas fa-forward"></i> Niet nu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const platform = '{{ $platform }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let excludedGames = [];
    let currentGameId = null;

    window.openRecommendation = async function() {
        const modal = document.getElementById('recommendationModal');
        const loading = document.getElementById('recommendationLoading');
        const empty = document.getElementById('recommendationEmpty');
        const game = document.getElementById('recommendationGame');

        modal.classList.add('active');
        loading.style.display = 'flex';
        empty.style.display = 'none';
        game.style.display = 'none';

        await fetchRecommendation();
    };

    window.closeRecommendation = function() {
        document.getElementById('recommendationModal').classList.remove('active');
    };

    async function fetchRecommendation() {
        const loading = document.getElementById('recommendationLoading');
        const empty = document.getElementById('recommendationEmpty');
        const game = document.getElementById('recommendationGame');

        try {
            const url = `/recommend/${platform}?excluded=${excludedGames.join(',')}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            loading.style.display = 'none';

            if (!data.success || !data.game) {
                empty.style.display = 'flex';
                return;
            }

            currentGameId = data.game.id;
            populateGameCard(data.game);
            game.style.display = 'block';

        } catch (error) {
            console.error('Error fetching recommendation:', error);
            loading.style.display = 'none';
            empty.style.display = 'flex';
        }
    }

    function populateGameCard(gameData) {
        const image = document.getElementById('recommendationImage');
        if (gameData.image_url) {
            image.src = gameData.image_url;
            image.alt = gameData.name;
            image.style.display = 'block';
        } else {
            image.style.display = 'none';
        }

        document.getElementById('recommendationName').textContent = gameData.name;

        const price = gameData.price ? `€${parseFloat(gameData.price).toFixed(2).replace('.', ',')}` : 'Gratis';
        document.getElementById('recommendationPrice').textContent = price;

        const playtime = gameData.playtime_hours > 0 ? `${gameData.playtime_hours}h` : 'Nog niet';
        document.getElementById('recommendationPlaytime').textContent = playtime;

        const lastPlayed = gameData.last_played_ago || 'Nooit';
        document.getElementById('recommendationLastPlayed').textContent = lastPlayed;

        // Build reason text
        let reasons = [];
        if (gameData.price && gameData.playtime_hours < 1) {
            reasons.push(`Je hebt €${parseFloat(gameData.price).toFixed(2).replace('.', ',')} betaald maar nog nauwelijks gespeeld`);
        }
        if (!gameData.last_played_at) {
            reasons.push('Nog nooit gespeeld');
        } else if (gameData.last_played_ago) {
            reasons.push(`${gameData.last_played_ago} geleden gespeeld`);
        }
        if (gameData.backlog_status_label) {
            reasons.push(`Status: ${gameData.backlog_status_label}`);
        }

        const reasonText = reasons.length > 0 ? reasons.join(' • ') : 'Gebaseerd op prijs en speeltijd';
        document.getElementById('recommendationReasonText').textContent = reasonText;
    }

    window.acceptRecommendation = async function() {
        if (!currentGameId) return;

        try {
            const response = await fetch(`/recommend/${platform}/${currentGameId}/accept`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                closeRecommendation();
                // Reload page to show updated backlog status
                window.location.reload();
            }
        } catch (error) {
            console.error('Error accepting recommendation:', error);
        }
    };

    window.rejectRecommendation = async function() {
        if (currentGameId) {
            excludedGames.push(currentGameId);
        }

        // Fetch next recommendation
        const loading = document.getElementById('recommendationLoading');
        const game = document.getElementById('recommendationGame');

        loading.style.display = 'flex';
        game.style.display = 'none';

        await fetchRecommendation();
    };

    // Close on overlay click
    document.getElementById('recommendationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRecommendation();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRecommendation();
        }
    });
})();
</script>
@endpush
