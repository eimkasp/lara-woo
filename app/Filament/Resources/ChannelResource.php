<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\RelationManagers\CustomerRelationManager;
use Filament\Forms;
use Filament\Tables;
use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers\ProductRelationManager;
use App\Filament\Resources\ChannelResource\RelationManagers\OrderChannelRelationManager;
use App\Models\Channel;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    // protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function getWidgets(): array
    {
        return [
            // Add any widgets you need here
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_url')
                    ->required()
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('consumer_key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('consumer_secret')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('base_url')->sortable()->searchable(),
            ])
            ->filters([
                // Add any filters you need here
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
            'view' => Pages\ViewChannel::route('/{record}'), // Register the view page
        ];
    }

    public static function getRelations(): array
    {
        return [
            ProductRelationManager::class, // Register the ProductRelationManager
            OrderChannelRelationManager::class, // Register the OrderRelationManager
            // CustomerRelationManager::class, // Register the CustomerRelationManager
        ];
    }
}