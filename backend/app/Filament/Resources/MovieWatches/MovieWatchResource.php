<?php

namespace App\Filament\Resources\MovieWatches;

use App\Filament\Resources\MovieWatches\Pages\ManageMovieWatches;
use App\Models\MovieWatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MovieWatchResource extends Resource
{
    protected static ?string $model = MovieWatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('movie.title')->label('Movie')->searchable()->sortable(),
                TextColumn::make('watched_at')->dateTime()->sortable(),
                TextColumn::make('runtime')->numeric()->sortable(),
                TextColumn::make('watch_count')->numeric()->sortable(),
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
            'index' => ManageMovieWatches::route('/'),
        ];
    }
}
