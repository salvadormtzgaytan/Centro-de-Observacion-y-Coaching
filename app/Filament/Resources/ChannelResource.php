<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers;
use App\Models\Channel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChannelResource extends Resource
{
    // — Menú en español dentro de "Catálogos" —
    protected static ?string $navigationGroup       = 'Catálogos';
    protected static ?int    $navigationSort        = 2;
    protected static ?string $navigationLabel       = 'Canales';
    protected static ?string $modelLabel            = 'Canal';
    protected static ?string $pluralModelLabel      = 'Canales';

    protected static ?string $model          = Channel::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label(__('channel.fields.key'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label(__('channel.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('order')
                    ->label(__('channel.fields.order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('channel.fields.key'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('channel.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('channel.fields.order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('channel.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('channel.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}
