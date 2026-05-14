<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Person;
use App\Models\TvSeries;
use Illuminate\Database\Eloquent\Model;

class PersonSyncService
{
    private const MAX_CAST = 15;

    private const MAX_TV_DIRECTORS = 5;

    private const MIN_TV_EPISODES = 5;

    private const MIN_TV_POPULARITY = 3.0;

    /**
     * @param  array<string, mixed>  $credits
     */
    public function syncForMovie(Movie $movie, array $credits): void
    {
        $this->sync($movie, $credits);
    }

    /**
     * @param  array<string, mixed>  $aggregateCredits
     */
    public function syncForTvSeries(TvSeries $series, array $aggregateCredits): void
    {
        $this->syncAggregate($series, $aggregateCredits);
    }

    /**
     * @param  array<string, mixed>  $aggregateCredits
     */
    private function syncAggregate(TvSeries $mediable, array $aggregateCredits): void
    {
        $pivotData = [];

        $cast = collect($aggregateCredits['cast'] ?? [])
            ->filter(fn ($m) => ($m['total_episode_count'] ?? 0) >= self::MIN_TV_EPISODES
                || ($m['popularity'] ?? 0) >= self::MIN_TV_POPULARITY)
            ->sortByDesc('total_episode_count');

        foreach ($cast as $member) {
            if (empty($member['id']) || empty($member['name'])) {
                continue;
            }

            $person = $this->upsertPerson($member);
            $character = $member['roles'][0]['character'] ?? null;

            $pivotData[$person->id] = [
                'character' => $character,
                'department' => 'Acting',
                'job' => null,
                'cast_order' => $member['order'] ?? null,
                'episode_count' => $member['total_episode_count'] ?? null,
            ];
        }

        $directors = collect($aggregateCredits['crew'] ?? [])
            ->filter(fn ($m) => ! empty($m['id']) && ! empty($m['name'])
                && collect($m['jobs'] ?? [])->contains('job', 'Director'))
            ->sortByDesc('total_episode_count')
            ->take(self::MAX_TV_DIRECTORS);

        foreach ($directors as $member) {
            $person = $this->upsertPerson($member);

            if (isset($pivotData[$person->id])) {
                continue;
            }

            $pivotData[$person->id] = [
                'character' => null,
                'department' => $member['department'] ?? 'Directing',
                'job' => 'Director',
                'cast_order' => null,
            ];
        }

        $mediable->people()->sync($pivotData);
    }

    /**
     * @param  Movie|TvSeries  $mediable
     * @param  array<string, mixed>  $credits
     */
    private function sync(Model $mediable, array $credits): void
    {
        $pivotData = [];

        foreach (array_slice($credits['cast'] ?? [], 0, self::MAX_CAST) as $member) {
            if (empty($member['id']) || empty($member['name'])) {
                continue;
            }

            $person = $this->upsertPerson($member);

            $pivotData[$person->id] = [
                'character' => $member['character'] ?? null,
                'department' => 'Acting',
                'job' => null,
                'cast_order' => $member['order'] ?? null,
            ];
        }

        foreach ($credits['crew'] ?? [] as $member) {
            if (empty($member['id']) || empty($member['name'])) {
                continue;
            }

            if (($member['job'] ?? '') !== 'Director') {
                continue;
            }

            $person = $this->upsertPerson($member);

            if (isset($pivotData[$person->id])) {
                continue;
            }

            $pivotData[$person->id] = [
                'character' => null,
                'department' => $member['department'] ?? 'Directing',
                'job' => $member['job'],
                'cast_order' => null,
            ];
        }

        $mediable->people()->sync($pivotData);
    }

    /**
     * @param  array<string, mixed>  $member
     */
    private function upsertPerson(array $member): Person
    {
        return Person::updateOrCreate(
            ['tmdb_id' => $member['id']],
            [
                'name' => $member['name'],
                'profile_path' => $member['profile_path'] ?? null,
            ]
        );
    }
}
