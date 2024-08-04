<?php

namespace App\Filament\Resources;

use App\Filament\Widgets\OrderSummaryWidget;
use Filament\Forms;
use Filament\Tables;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ProductRelationManager;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getWidgets(): array
    {
        return [
            OrderSummaryWidget::class, // Include the summary widget on the index page
        ];
    }

    // Add this method to define the badge
    public static function getNavigationBadge(): ?string
    {
        return (string) Order::count(); // Example: showing total order count as a badge
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'email')
                    ->required(),
                Forms\Components\Select::make('channel_id')
                    ->relationship('channel', 'name')
                    ->required(),
                // Forms\Components\Repeater::make('products')
                //     ->relationship('products')
                //     ->schema([
                //         Forms\Components\TextInput::make('name')
                //             ->disabled(),
                //         Forms\Components\TextInput::make('pivot.quantity')
                //             ->label('Quantity')
                //             ->required()
                //             ->default(fn ($record) => $record?->pivot?->quantity ?? 0),
                //     ])
                //     ->columns(2),
                Forms\Components\Repeater::make('meta')
                    ->relationship('meta')
                    ->schema([
                        Forms\Components\TextInput::make('key')->disabled(),
                        Forms\Components\TextInput::make('value')->disabled(),
                    ])
                    ->columns(2)
                    ->label('Meta Data')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('total')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('customer.email')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('products.name')
                    ->label('Products')
                    ->sortable(),
            ])
            ->filters([
                // Add any filters you need here
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'), // Register the view page
        ];
    }

    public static function getRelations(): array
    {
        return [
            ProductRelationManager::class, // Register the ProductRelationManager
        ];
    }
}