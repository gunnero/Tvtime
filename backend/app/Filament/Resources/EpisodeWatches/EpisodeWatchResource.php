<?php

namespace App\Filament\Resources\EpisodeWatches;

use App\Filament\Resources\EpisodeWatches\Pages\ManageEpisodeWatches;
use App\Models\EpisodeWatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EpisodeWatchResource extends Resource
{
    protected static ?string $model = EpisodeWatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('show.title')->label('Show')->searchable()->sortable(),
                TextColumn::make('episode.season_number')->label('Season')->numeric()->sortable(),
                TextColumn::make('episode.episode_number')->label('Episode')->numeric()->sortable(),
                TextColumn::make('watched_at')->dateTime()->sortable(),
                TextColumn::make('runtime')->numeric()->sortable(),
                TextColumn::make('source')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('watched_at', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEpisodeWatches::route('/'),
        ];
    }
}
