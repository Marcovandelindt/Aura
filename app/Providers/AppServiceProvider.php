<?php

namespace App\Providers;

use App\Services\Lastfm\LastfmService;
use Chiiya\LaravelTmdb\TmdbClient;
use Chiiya\Tmdb\Http\ClientInterface;
use Chiiya\Tmdb\Repositories\MovieRepository;
use Chiiya\Tmdb\Repositories\SearchRepository;
use Chiiya\Tmdb\Repositories\TvEpisodeRepository;
use Chiiya\Tmdb\Repositories\TvSeasonRepository;
use Chiiya\Tmdb\Repositories\TvShowRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientInterface::class, function () {
            return new class extends TmdbClient
            {
                protected function createClient(): PendingRequest
                {
                    return Http::acceptJson()
                        ->withoutVerifying()
                        ->retry(2)
                        ->baseUrl('https://api.themoviedb.org/3/')
                        ->withToken(config('tmdb.token'));
                }
            };
        });

        $this->app->singleton(MovieRepository::class, function ($app) {
            return new MovieRepository($app->make(ClientInterface::class));
        });

        $this->app->singleton(TvShowRepository::class, function ($app) {
            return new TvShowRepository($app->make(ClientInterface::class));
        });

        $this->app->singleton(TvSeasonRepository::class, function ($app) {
            return new TvSeasonRepository($app->make(ClientInterface::class));
        });

        $this->app->singleton(TvEpisodeRepository::class, function ($app) {
            return new TvEpisodeRepository($app->make(ClientInterface::class));
        });

        $this->app->singleton(SearchRepository::class, function ($app) {
            return new SearchRepository($app->make(ClientInterface::class));
        });

        $this->app->singleton(LastfmService::class, fn () => LastfmService::make());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isLocal()) {
            Http::globalOptions(['verify' => false]);
        }

        Paginator::defaultView('vendor.pagination.custom');

        view()->composer('layouts.app', \App\View\Composers\SpotifyComposer::class);
        view()->composer('*', \App\View\Composers\ThemeComposer::class);
    }
}
