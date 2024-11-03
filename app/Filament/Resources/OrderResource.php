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
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\RepeatableEntry;

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(['default' => 1, 'sm' => 3])
                    ->schema([
                        Grid::make()
                            ->schema([
                                Section::make('Order Summary')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('id')
                                                ->label('Order ID')
                                                ->weight(FontWeight::Bold),
                                            TextEntry::make('created_at')
                                                ->label('Order Date')
                                                ->dateTime(),
                                            TextEntry::make('status')
                                                ->badge()
                                                ->color(fn (string $state): string => match ($state) {
                                                    'completed' => 'success',
                                                    'processing' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary',
                                                }),
                                            TextEntry::make('total')
                                                ->label('Order Total')
                                                ->money('USD')
                                                ->weight(FontWeight::Bold)
                                                ->color('success'),
                                        ]),
                                    ]),

                                Section::make('Customer Details')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('customer.email')
                                                ->label('Email')
                                                ->copyable(),
                                            TextEntry::make('customer.phone')
                                                ->label('Phone')
                                                ->copyable(),
                                        ]),
                                    ]),

                                Section::make('Products')
                                    ->schema([
                                        RepeatableEntry::make('products')
                                            ->schema([
                                                Grid::make(3)->schema([
                                                    TextEntry::make('name')
                                                        ->label('Product'),
                                                    TextEntry::make('pivot.quantity')
                                                        ->label('Quantity'),
                                                    TextEntry::make('price')
                                                        ->label('Unit Price')
                                                        ->money('USD'),
                                                ]),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make()
                            ->schema([
                                Tabs::make('Address Information')
                                    ->tabs([
                                        Tab::make('Billing Address')
                                            ->schema([
                                                TextEntry::make('billing_first_name')
                                                    ->label('First Name'),
                                                TextEntry::make('billing_last_name')
                                                    ->label('Last Name'),
                                                TextEntry::make('billing_address_1')
                                                    ->label('Address Line 1'),
                                                TextEntry::make('billing_address_2')
                                                    ->label('Address Line 2'),
                                                TextEntry::make('billing_city')
                                                    ->label('City'),
                                                TextEntry::make('billing_state')
                                                    ->label('State'),
                                                TextEntry::make('billing_postcode')
                                                    ->label('Postal Code'),
                                                TextEntry::make('billing_country')
                                                    ->label('Country'),
                                                TextEntry::make('billing_phone')
                                                    ->label('Phone')
                                                    ->copyable(),
                                            ]),
                                        
                                        Tab::make('Shipping Address')
                                            ->schema([
                                                TextEntry::make('shipping_method')
                                                    ->label('Shipping Method'),
                                                TextEntry::make('shipping_total')
                                                    ->label('Shipping Cost')
                                                    ->money('USD'),
                                                TextEntry::make('shipping_first_name')
                                                    ->label('First Name'),
                                                TextEntry::make('shipping_last_name')
                                                    ->label('Last Name'),
                                                TextEntry::make('shipping_address_1')
                                                    ->label('Address Line 1'),
                                                TextEntry::make('shipping_address_2')
                                                    ->label('Address Line 2'),
                                                TextEntry::make('shipping_city')
                                                    ->label('City'),
                                                TextEntry::make('shipping_state')
                                                    ->label('State'),
                                                TextEntry::make('shipping_postcode')
                                                    ->label('Postal Code'),
                                                TextEntry::make('shipping_country')
                                                    ->label('Country'),
                                            ]),
                                    ]),

                                Section::make('Channel Information')
                                    ->schema([
                                        TextEntry::make('channel.name')
                                            ->label('Channel')
                                            ->badge(),
                                        TextEntry::make('woocommerce_id')
                                            ->label('WooCommerce ID')
                                            ->copyable(),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}