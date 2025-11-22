<?php

namespace App\Services\TMDB;

class TMDBImageService
{
    public function getPosterUrl(?string $path, string $size = 'medium'): ?string
    {
        if (! $path) {
            return null;
        }

        $sizeConfig = config("tmdb.poster_sizes.{$size}", config('tmdb.poster_sizes.medium'));

        return config('tmdb.image_base_url').$sizeConfig.$path;
    }

    public function getBackdropUrl(?string $path, string $size = 'large'): ?string
    {
        if (! $path) {
            return null;
        }

        $sizeConfig = config("tmdb.backdrop_sizes.{$size}", config('tmdb.backdrop_sizes.large'));

        return config('tmdb.image_base_url').$sizeConfig.$path;
    }

    public function getProfileUrl(?string $path, string $size = 'medium'): ?string
    {
        if (! $path) {
            return null;
        }

        $sizeConfig = config("tmdb.profile_sizes.{$size}", config('tmdb.profile_sizes.medium'));

        return config('tmdb.image_base_url').$sizeConfig.$path;
    }

    public function getStillUrl(?string $path, string $size = 'medium'): ?string
    {
        return $this->getBackdropUrl($path, $size);
    }
}
