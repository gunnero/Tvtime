<?php

namespace App\Filament\Resources\Shows;

use App\Filament\Resources\Shows\Pages\ManageShows;
use App\Models\Show;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShowResource extends Resource
{
    protected static ?string $model = Show::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                IconColumn::make('followed')->boolean()->sortable(),
                TextColumn::make('seen_episodes')->numeric()->sortable(),
                TextColumn::make('aired_episodes')->numeric()->sortable(),
                TextColumn::make('runtime')->label('Runtime')->numeric()->sortable(),
                TextColumn::make('latest_seen_at')->dateTime()->sortable(),
                TextColumn::make('external_source')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('external_id')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('followed'),
            ])
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
            'index' => ManageShows::route('/'),
        ];
    }
}
