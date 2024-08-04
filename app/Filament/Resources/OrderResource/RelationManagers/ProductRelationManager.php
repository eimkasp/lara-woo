<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms;
use Filament\Forms\Form;

class ProductRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public  function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Quantity')
                    ->sortable()
                    ->searchable(),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->disabled(),
                Forms\Components\TextInput::make('pivot.quantity')
                    ->label('Quantity')
                    ->required(),
            ]);
    }
}