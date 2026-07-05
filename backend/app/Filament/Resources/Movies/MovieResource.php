<?php

namespace App\Filament\Resources\Movies;

use App\Filament\Resources\Movies\Pages\ManageMovies;
use App\Models\Movie;
use App\Services\MediaMetadataService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MovieResource extends Resource
{
    protected static ?string $model = Movie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('poster_preview')
                    ->label('Poster')
                    ->getStateUsing(fn (Movie $record): ?string => $record->poster_path
                        ? rtrim((string) config('tmdb.image_base_url'), '/').'/w92/'.ltrim($record->poster_path, '/')
                        : $record->poster_url)
                    ->imageHeight(54)
                    ->toggleable(),
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('tmdb_id')->label('TMDB')->sortable()->toggleable(),
                TextColumn::make('metadata_status')
                    ->label('Metadata')
                    ->badge()
                    ->getStateUsing(fn (Movie $record): string => $record->metadata_refreshed_at ? 'enriched' : 'local'),
                IconColumn::make('is_to_watch')->label('To watch')->boolean()->sortable(),
                TextColumn::make('runtime')->numeric()->sortable(),
                TextColumn::make('metadata_refreshed_at')->label('Refreshed')->dateTime()->sortable()->toggleable(),
                TextColumn::make('external_source')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('external_id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_to_watch')->label('To watch'),
            ])
            ->recordActions([
                Action::make('refreshMetadata')
                    ->label('Refresh metadata')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->action(fn (Movie $record): array => app(MediaMetadataService::class)->enrichMovie($record)),
            ])
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
            'index' => ManageMovies::route('/'),
        ];
    }
}
