# Gamified Taken — Plan

## Concept

Een to-do lijst die aanvoelt als een RPG. Elke taak die je afrondt levert XP op, je stijgt in level, bouwt streaks op en ontgrendelt achievements. Het doel is niet productiviteit meten maar het gevoel van vooruitgang tastbaar maken.

---

## Kern features

### XP & Levels
- Elke taak heeft een moeilijkheidsgraad met bijbehorende XP-beloning:
  - **Makkelijk** — 10 XP (bijv. vaat doen, boodschap sturen)
  - **Normaal** — 25 XP (bijv. stofzuigen, 30 min wandelen)
  - **Zwaar** — 60 XP (bijv. grote opruimbeurt, lange wandeling)
- Level-drempelwaarden lopen exponentieel op zodat het uitdagend blijft:
  - Level 1 → 2: 100 XP
  - Level 2 → 3: 250 XP
  - Level 3 → 4: 450 XP
  - *(elke stap ~1.6× zwaarder)*
- Bij een level-up verschijnt er een visuele melding in het dashboard

### Streaks
- Een streak loopt op als je elke dag minimaal één taak afrondt
- Streak-bonus: +10% XP per streak-dag (max +50%)
- Streak gebroken? De teller reset naar 0, maar de langste streak wordt bijgehouden

### Categorieën
| Icoon | Naam | Voorbeelden |
|-------|------|-------------|
| 🏠 | Huis | Vaat, stofzuigen, opruimen, was |
| 🚶 | Beweging | Wandelen, fietsen, sporten |
| 🧘 | Welzijn | Mediteren, vroeg naar bed, hydrateren |
| 🎯 | Persoonlijk | Iets regelen, bellen, lezen |
| 💼 | Werk | Taak afmaken, mail beantwoorden |
| ⭐ | Overig | Alles wat niet past |

### Taaktypen
- **Eenmalig** — Verdwijnt na voltooiing
- **Dagelijks** — Reset elke dag om middernacht (bijv. "30 min wandelen")
- **Wekelijks** — Reset elke maandag

### Achievements
Achievements ontgrendelen bij mijlpalen en geven een eenmalige XP-bonus:

| Achievement | Omschrijving | Bonus |
|------------|--------------|-------|
| Eerste stap | Eerste taak afgerond | +25 XP |
| Op stoom | 3 dagen streak | +50 XP |
| Doorzetter | 7 dagen streak | +100 XP |
| Huisbaas | 10 huistaken afgerond | +75 XP |
| Wandelaar | 10 bewegingstaken afgerond | +75 XP |
| Level 5 | Level 5 bereikt | +150 XP |
| Honderd | 100 taken afgerond totaal | +200 XP |

---

## Database

### `tasks`
| Kolom | Type | Omschrijving |
|-------|------|--------------|
| id | bigint | |
| title | string(255) | |
| description | text\|null | |
| category | enum | huis, beweging, welzijn, persoonlijk, werk, overig |
| difficulty | enum | easy, normal, hard |
| type | enum | once, daily, weekly |
| completed_at | timestamp\|null | null = nog open |
| due_date | date\|null | optionele deadline |
| timestamps | | |

### `player_stats`
Één rij (de gebruiker heeft één stats-profiel):
| Kolom | Type | Omschrijving |
|-------|------|--------------|
| id | bigint | |
| total_xp | int | cumulatief totaal |
| level | int | huidige level |
| current_streak | int | huidige dagstreak |
| longest_streak | int | record streak |
| last_active_date | date\|null | voor streak-berekening |
| tasks_completed | int | totaal afgeronde taken |
| timestamps | | |

### `achievements`
Seeded (vaste lijst):
| Kolom | Type |
|-------|------|
| id | bigint |
| key | string | unieke identifier |
| name | string | |
| description | string | |
| icon | string | emoji |
| xp_bonus | int | |

### `unlocked_achievements`
| Kolom | Type |
|-------|------|
| id | bigint |
| achievement_id | bigint FK |
| unlocked_at | timestamp |

---

## UI

### Dashboard-widget (op de hoofdpagina)
- Level badge + naam (bijv. "Level 4 — Avonturier")
- XP-balk met voortgang naar volgend level
- Streak-teller met vuuricoontje
- Aantal open taken vandaag

### Taken-pagina (`/taken`)
- Bovenaan: level, XP-balk, streak — altijd zichtbaar
- Taken gesplitst in **Vandaag** (dagelijks + eenmalig met deadline vandaag) en **Later**
- Per taak: titel, categorie-badge, moeilijkheidsgraad, XP-waarde
- Grote "Voltooien" knop — bij klik verschijnt een XP-animatie (+25 XP ✨)
- Knop om nieuwe taak toe te voegen (modal)
- Achievements-sectie onderaan met vergrendelde/ontgrendelde staat

### Achievement-popup
Bij het ontgrendelen van een achievement verschijnt er een overlay-popup met icoon, naam en XP-bonus.

---

## Routes & Controllers

```
GET  /taken                    TaakController@index
POST /taken                    TaakController@store
POST /taken/{taak}/voltooien   TaakController@complete
DELETE /taken/{taak}           TaakController@destroy
```

---

## Implementatievolgorde

1. Migraties + modellen (`Task`, `PlayerStats`, `Achievement`, `UnlockedAchievement`)
2. Seeder voor achievements
3. `PlayerStatsService` — berekent XP, level-ups, streaks, achievement-checks
4. `TaakController` met index, store, complete, destroy
5. Taken-pagina view met XP-animatie
6. Achievement-popup
7. Dashboard-widget op de hoofdpagina
