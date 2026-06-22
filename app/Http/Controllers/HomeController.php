<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\EpisodeWatch;
use App\Models\Expense;
use App\Models\HealthEntry;
use App\Models\Play;
use App\Models\PlayStationSession;
use App\Services\Spotify\SpotifyService;
use App\Services\WeatherService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected SpotifyService $spotifyService,
        protected WeatherService $weatherService,
    ) {}

    public function index(): View
    {
        $spotifyConnected = $this->spotifyService->isConnected();

        $songsThisWeek = $this->getSongsThisWeekStats();
        $expenseStats = $this->getExpenseStats();
        $lastPlayedTrack = $this->getLastPlayedTrack();
        $lastEpisodeWatch = $this->getLastEpisodeWatch();
        $lastGameSession = $this->getLastGameSession();
        $lastHealthEntry = $this->getLastHealthEntry();
        $lastExpense = $this->getLastExpense();
        $lastStravaActivity = $this->getLastStravaActivity();
        $weather = $this->weatherService->get();

        return view('home.index', compact(
            'spotifyConnected',
            'songsThisWeek',
            'expenseStats',
            'lastPlayedTrack',
            'lastEpisodeWatch',
            'lastGameSession',
            'lastHealthEntry',
            'lastExpense',
            'lastStravaActivity',
            'weather',
        ));
    }

    private function getSongsThisWeekStats(): array
    {
        $startOfThisWeek = Carbon::now()->startOfWeek();
        $endOfThisWeek = Carbon::now()->endOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekCount = Play::whereBetween('played_at', [$startOfThisWeek, $endOfThisWeek])->count();
        $lastWeekCount = Play::whereBetween('played_at', [$startOfLastWeek, $endOfLastWeek])->count();

        $difference = $thisWeekCount - $lastWeekCount;
        $changeText = '';
        $changeClass = '';

        if ($difference > 0) {
            $changeText = "+{$difference} from last week";
            $changeClass = 'positive';
        } elseif ($difference < 0) {
            $changeText = abs($difference).' fewer than last week';
            $changeClass = 'negative';
        } else {
            $changeText = 'Same as last week';
            $changeClass = '';
        }

        return [
            'count' => $thisWeekCount,
            'change_text' => $changeText,
            'change_class' => $changeClass,
            'last_week_count' => $lastWeekCount,
        ];
    }

    private function getLastPlayedTrack(): ?object
    {
        $row = DB::table('plays')
            ->join('tracks', 'tracks.id', '=', 'plays.track_id')
            ->leftJoin('albums', 'albums.id', '=', 'tracks.album_id')
            ->leftJoin('track_artists', function ($join) {
                $join->on('track_artists.track_id', '=', 'tracks.id')
                    ->where('track_artists.is_primary', true);
            })
            ->leftJoin('artists', 'artists.id', '=', 'track_artists.artist_id')
            ->select(
                'plays.played_at',
                'tracks.title as track_name',
                'tracks.spotify_track_id',
                'albums.name as album_name',
                'albums.image_url as album_image_url',
                DB::raw("JSON_ARRAY(COALESCE(artists.name, '')) as artist_names"),
            )
            ->orderByDesc('plays.played_at')
            ->first();

        if (! $row) {
            return null;
        }

        $row->played_at = Carbon::parse($row->played_at);
        $row->played_at_human = $row->played_at->diffForHumans();
        $row->artist_names = json_decode($row->artist_names, true) ?? [];

        return $row;
    }

    private function getLastEpisodeWatch(): ?EpisodeWatch
    {
        return EpisodeWatch::with(['episode.season.series'])
            ->orderByDesc('watched_at')
            ->first();
    }

    private function getLastGameSession(): ?PlayStationSession
    {
        return PlayStationSession::with('game')
            ->orderByDesc('started_at')
            ->first();
    }

    private function getLastHealthEntry(): ?HealthEntry
    {
        return HealthEntry::whereNotNull('steps')
            ->orderByDesc('date')
            ->first();
    }

    private function getExpenseStats(): array
    {
        $thisWeek = Expense::thisWeek()->sum('amount');

        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();
        $lastWeek = Expense::whereBetween('date', [$startOfLastWeek, $endOfLastWeek])->sum('amount');

        $change = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : 0;

        return [
            'total' => $thisWeek,
            'formatted_total' => '€'.number_format($thisWeek, 2, ',', '.'),
            'change_text' => ($change >= 0 ? '+' : '').round($change).'% vs vorige week',
            'change_class' => $change > 10 ? 'negative' : ($change < -10 ? 'positive' : 'neutral'),
        ];
    }

    private function getLastStravaActivity(): ?Activity
    {
        return Activity::orderByDesc('start_date')->first();
    }

    private function getLastExpense(): ?Expense
    {
        return Expense::with('category')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->first();
    }
}
