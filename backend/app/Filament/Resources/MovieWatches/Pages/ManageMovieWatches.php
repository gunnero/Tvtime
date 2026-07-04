<?php

namespace App\Filament\Resources\MovieWatches\Pages;

use App\Filament\Resources\MovieWatches\MovieWatchResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMovieWatches extends ManageRecords
{
    protected static string $resource = MovieWatchResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
