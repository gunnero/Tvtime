<?php

namespace App\Filament\Resources\Alerts;

use App\Filament\Resources\Alerts\Pages\ManageAlerts;
use App\Models\Alert;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')->numeric()->required(),
                TextInput::make('category')->required()->maxLength(255),
                TextInput::make('title')->required()->maxLength(255),
                TextInput::make('subtitle')->required()->maxLength(255),
                TextInput::make('due_text')->required()->maxLength(255),
                KeyValue::make('payload'),
                Toggle::make('unread'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable()->sortable(),
                TextColumn::make('category')->badge()->searchable()->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                IconColumn::make('unread')->boolean()->sortable(),
                TextColumn::make('read_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category'),
                TernaryFilter::make('unread'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAlerts::route('/'),
        ];
    }
}
