<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return (string) Product::count(); // Example: showing total product count as a badge
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required(),
                Forms\Components\TextInput::make('stock_quantity'),
                Forms\Components\Select::make('channel_id')
                    ->relationship('channel', 'name')
                    ->required(),
                Forms\Components\Repeater::make('variations')
                    ->relationship('variations')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->required(),
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\TextInput::make('price')
                            ->required(),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->required(),
                    ])
                    ->columns(2)
                    ->required(),
                Forms\Components\Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Image URL')
                            ->required(),
                        Forms\Components\Checkbox::make('is_primary')
                            ->label('Primary Image'),
                    ])
                    ->columns(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image_url')
                    ->label('Image Preview')
                    ->sortable()
                    ->disk('public')
                    ->width(100)
                    ->height(100),
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('sku')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')->sortable(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('variations.sku')
                    ->label('Variation SKUs')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->relationship('channel', 'name')
                    ->label('Channel'),
            ], layout: FiltersLayout::AboveContentCollapsible);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'), // Register the view page
        ];
    }
}