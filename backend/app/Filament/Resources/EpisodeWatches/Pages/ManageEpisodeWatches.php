<?php

namespace App\Filament\Resources\EpisodeWatches\Pages;

use App\Filament\Resources\EpisodeWatches\EpisodeWatchResource;
use Filament\Resources\Pages\ManageRecords;

class ManageEpisodeWatches extends ManageRecords
{
    protected static string $resource = EpisodeWatchResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
