# Music Schema Refactoring — Testing Plan

## Wat er gedaan is

De volledige muziek-database is genormaliseerd. De oude platte tabellen zijn vervangen door een relationeel schema.

### Oude tabellen (nu verwijderd)
| Tabel | Wat ermee gebeurd is |
|---|---|
| `played_tracks` | Vervangen door `plays` + `tracks` + `artists` + `albums` |
| `lastfm_scrobbles` | Plays gemigreerd naar `plays` (source='lastfm'), track-info naar `tracks` |
| `played_track_mood` | Vervangen door `track_mood` (track_id FK i.p.v. spotify_track_id string) |

### Nieuwe tabellen
| Tabel | Inhoud |
|---|---|
| `artists` | 7.704 artiesten |
| `albums` | 15.997 albums |
| `tracks` | 19.560 nummers (met optioneel `spotify_track_id`) |
| `track_artists` | pivot: track ↔ artiest (met `is_primary` en `sort_order`) |
| `plays` | 97.744 plays (`source` = `spotify` of `lastfm`) |
| `track_mood` | pivot: track ↔ mood (met `game_id`) |

### Gewijzigde bestanden
- `app/Services/Spotify/SpotifyTrackService.php` — schrijft nu naar nieuwe tabellen
- `app/Jobs/ImportLastfmScrobblesJob.php` — schrijft nu naar nieuwe tabellen
- `app/Jobs/EnrichLastfmTracksJob.php` — verrijkt nu `tracks.duration_ms`
- `app/Http/Controllers/MusicController.php`
- `app/Http/Controllers/MusicStatsController.php`
- `app/Http/Controllers/ArtistController.php`
- `app/Http/Controllers/TrackController.php`
- `app/Http/Controllers/LastfmTrackController.php`
- `app/Http/Controllers/AlbumController.php`
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/WeeklyController.php`
- `app/Http/Controllers/LastfmController.php`
- `app/Http/Controllers/MoodController.php`
- `app/Http/Controllers/GameController.php`
- `app/Http/Controllers/Strava/StravaController.php`
- `app/Models/Mood.php` — `tracks()` relatie naar `track_mood`
- `app/Models/Game.php` — `PlayedTrackMood` referentie verwijderd

### Verwijderde bestanden
- `app/Models/PlayedTrack.php`
- `app/Models/PlayedTrackMood.php`
- `app/Models/LastfmScrobble.php`

---

## Testplan

Start de dev-server: `composer run dev`

### 1. Homepage
- [ ] Laadt zonder fouten
- [ ] "Tracks deze week" toont een getal
- [ ] "Laatste gespeeld" toont track + artiest + albumhoes
- [ ] Overige widgets (series, gaming, health, expenses) laden normaal

### 2. Music — overzicht (`/music`)
- [ ] Lijst met plays laadt (met pagination)
- [ ] Filteren op Today / This week / This month werkt
- [ ] Zoeken op tracknaam, album, artiest werkt
- [ ] Stats bovenaan (totaal, uniek, duur) kloppen
- [ ] "Top artist this week" widget rechts toont artiest + statistieken

### 3. Music — top tracks (`/music/top-tracks`)
- [ ] Laadt zonder fouten
- [ ] Period-filter (today/week/month/all) werkt

### 4. Music — stats (`/music/stats`)
- [ ] Alle kaarten laden zonder fouten
- [ ] Top Artists (10 artiesten met play count)
- [ ] Top Tracks (10 nummers)
- [ ] Top Albums (10 albums)
- [ ] Listening stats (totaal uren, eerste play, gemiddeld per dag)
- [ ] Top listening times (3 tijdsloten)
- [ ] Weekday vs weekend verdeling
- [ ] Repeat ratio + meest herhaalde track
- [ ] Binge sessions (top 3 sessies)
- [ ] Discovery rate + grafiek afgelopen 7 dagen
- [ ] Top moods (**nieuw** — was eerder leeg)

### 5. Artiest-pagina (`/artists/{naam}`)
- [ ] Pagina laadt voor een bestaande artiest
- [ ] Unieke tracks met play counts
- [ ] Alle plays (history)
- [ ] Stats (eerste play, laatste play, totaal plays)
- [ ] 404 voor onbekende artiest

### 6. Track-pagina (`/tracks/{spotify_track_id}`)
- [ ] Pagina laadt voor een Spotify track
- [ ] Track info + artiest + album
- [ ] Play history
- [ ] Stats

### 7. Last.fm track (`/tracks/lastfm?artist=X&track=Y`)
- [ ] Laadt voor een bestaand Last.fm nummer
- [ ] 404 voor onbekend nummer

### 8. Album-pagina (`/albums/{naam}`)
- [ ] Pagina laadt voor een bestaand album
- [ ] Tracks met play counts
- [ ] Play history
- [ ] Stats

### 9. Last.fm beheer (`/lastfm`)
- [ ] Stats kloppen: geïmporteerde scrobbles, unieke tracks, tracks met duratie
- [ ] "Start import" knop werkt (dispatcht job)
- [ ] Import-voortgang polt correct
- [ ] "Tracks zonder duratie" link werkt
- [ ] "Verrijking starten" knop werkt
- [ ] "Opschonen" knop toont succes-melding (no-op in nieuw schema)
- [ ] "Alles verwijderen" verwijdert `plays` met source='lastfm'

### 10. Last.fm — tracks zonder duratie (`/lastfm/missing-duration`)
- [ ] Lijst van tracks zonder duratie laadt
- [ ] Status "Nog niet geprobeerd" vs "Niet gevonden" klopt
- [ ] "Ophalen via Last.fm" knop werkt per rij
- [ ] "Handmatig invullen" werkt per rij
- [ ] "Alle X op deze pagina ophalen" werkt

### 11. Last.fm — correcties (`/lastfm/corrections`)
- [ ] Top 100 meest gespeelde Last.fm tracks laadt
- [ ] Artiest-correcties opslaan werkt
- [ ] Zoeken naar tracks werkt

### 12. Moods
- [ ] Moods ophalen voor een track werkt (via track-pagina of JS call)
- [ ] Mood toevoegen aan track slaat op in `track_mood`
- [ ] Mood verwijderen werkt
- [ ] Game koppelen aan track werkt

### 13. Games (`/games/{slug}`)
- [ ] Lijst van games laadt
- [ ] Tracks per game laadt (via `track_mood.game_id`)
- [ ] Play count per track klopt

### 14. Strava — activiteit (`/strava/{id}`)
- [ ] Activiteitspagina laadt
- [ ] Tracks gespeeld tijdens activiteit worden getoond (via `plays.played_at` range)

### 15. Weekly overview (`/weekly`)
- [ ] Music-sectie: count, unieke tracks, top track, top artiest
- [ ] Overige secties ongewijzigd

---

## Bekende randgevallen / mogelijke problemen

### `duration_ms = 0` sentinel
Tracks waarbij Last.fm enrichment is geprobeerd maar niets gevonden: `duration_ms = 0`. Dit wordt weergegeven als `—` (via `getFormattedDurationAttribute`). Op de "Missing duration"-pagina staan deze als "Niet gevonden".

### Last.fm tracks zonder `spotify_track_id`
De meeste Last.fm-only tracks hebben geen `spotify_track_id`. De Track-pagina (`/tracks/{id}`) werkt alleen voor Spotify tracks. Last.fm tracks zijn bereikbaar via `/tracks/lastfm?artist=X&track=Y`.

### Artiest-matching is case-insensitive
`LOWER(name) = LOWER(?)` — artiesten met afwijkende hoofdletters (bijv. "dEUS" vs "Deus") worden samengevoegd bij import maar kunnen als aparte records bestaan als de data inconsistent was.

### Spotify sync (`/music/sync`)
Na de refactoring schrijft de Spotify sync naar de nieuwe tabellen. Test dit door te syncen en te controleren of nieuwe plays verschijnen in `/music`.

---

## Snelle DB-checks (via `php artisan tinker`)

```php
// Hoeveel plays per source?
DB::table('plays')->groupBy('source')->selectRaw('source, COUNT(*) as cnt')->get();

// Hoeveel tracks zonder duration en zonder spotify_track_id?
App\Models\Track::whereNull('duration_ms')->whereNull('spotify_track_id')->count();

// Hoeveel track_mood records gemigreerd?
DB::table('track_mood')->count();
```
