<?php

namespace App\Services;

use App\Models\EpisodeWatch;
use App\Models\MovieWatch;
use App\Models\Show;
use App\Models\User;

class MediaLibraryService
{
    /**
     * @return array{episodesWatched:int,moviesWatched:int,hoursWatched:int,showsFollowed:int}
     */
    public function statsFor(User $user): array
    {
        $episodeMinutes = (int) EpisodeWatch::forUser($user)->sum('runtime');
        $movieMinutes = (int) MovieWatch::forUser($user)->sum('runtime');

        return [
            'episodesWatched' => EpisodeWatch::forUser($user)->count(),
            'moviesWatched' => MovieWatch::forUser($user)->count(),
            'hoursWatched' => (int) round(($episodeMinutes + $movieMinutes) / 60),
            'showsFollowed' => Show::forUser($user)->followed()->count(),
        ];
    }
}
