<?php

namespace App\Filament\Resources\Invites;

use App\Enums\InviteStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Invites\Pages\ManageInvites;
use App\Models\Invite;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InviteResource extends Resource
{
    protected static ?string $model = Invite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('token_hash')->disabled()->dehydrated(false),
                Select::make('role')
                    ->options([
                        UserRole::Admin->value => 'Admin',
                        UserRole::Member->value => 'Member',
                    ])
                    ->required(),
                Select::make('status')
                    ->options([
                        InviteStatus::Pending->value => 'Pending',
                        InviteStatus::Accepted->value => 'Accepted',
                        InviteStatus::Expired->value => 'Expired',
                    ])
                    ->required(),
                DateTimePicker::make('expires_at')->required(),
                DateTimePicker::make('accepted_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('role')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
                TextColumn::make('accepted_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options([
                    UserRole::Admin->value => 'Admin',
                    UserRole::Member->value => 'Member',
                ]),
                SelectFilter::make('status')->options([
                    InviteStatus::Pending->value => 'Pending',
                    InviteStatus::Accepted->value => 'Accepted',
                    InviteStatus::Expired->value => 'Expired',
                ]),
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
            'index' => ManageInvites::route('/'),
        ];
    }
}
