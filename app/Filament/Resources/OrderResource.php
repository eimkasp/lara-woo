<?php 

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

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
                Forms\Components\Repeater::make('products')
                    ->relationship('products')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('products', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('pivot.quantity')
                            ->required(),
                    ])
                    ->columns(2)
                    ->required(),
                Forms\Components\Select::make('channel_id')
                    ->relationship('channel', 'name')
                    ->required(),
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
        ];
    }
}

