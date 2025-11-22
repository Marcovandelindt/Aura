<?php

namespace App\Providers;

use Chiiya\LaravelTmdb\TmdbClient;
use Chiiya\Tmdb\Http\ClientInterface;
use Chiiya\Tmdb\Repositories\MovieRepository;
use Chiiya\Tmdb\Repositories\SearchRepository;
use Chiiya\Tmdb\Repositories\TvEpisodeRepository;
use Chiiya\Tmdb\Repositories\TvSeasonRepository;
use Chiiya\Tmdb\Repositories\TvShowRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientInterface::class, TmdbClient::class);

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('layouts.app', \App\View\Composers\SpotifyComposer::class);
        view()->composer('*', \App\View\Composers\ThemeComposer::class);
    }
}
