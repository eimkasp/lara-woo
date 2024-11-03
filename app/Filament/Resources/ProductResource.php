<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;

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
                Forms\Components\Repeater::make('images')
                    ->label('Images')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Image URL')
                            ->url()
                            ->required(),
                    ])
                    ->createItemButtonLabel('Add Image')
                    ->required(),
                Forms\Components\Repeater::make('relatedProductsBySku')
                    ->relationship('relatedProductsBySku')
                    ->schema([
                        TextInput::make('name')->label('Related Product Name')->disabled(),
                        TextInput::make('channel_id')->label('Channel')->disabled(),
                        TextInput::make('price')->label('Price')->disabled(),
                    ])
                    ->columns(3)
                    ->label('Related Products from Other Channels'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => $record->images()->first()?->url)
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')) // Optional: add a placeholder
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

                Tables\Columns\TextColumn::make('relatedProductsBySku.name')
                    ->label('Related Products')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->relationship('channel', 'name')
                    ->label('Channel'),
            ], layout: FiltersLayout::AboveContentCollapsible);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                ])->schema([
                    Grid::make()
                        ->schema([
                            Section::make('Product Details')
                                ->schema([
                                    Grid::make(2)->schema([
                                        ImageEntry::make('primary_image_url')
                                            ->label('Product Image')
                                            ->height(200)
                                            ->extraAttributes(['class' => 'rounded-lg']),
                                        Grid::make()
                                            ->schema([
                                                TextEntry::make('sku')
                                                    ->label('SKU')
                                                    ->weight(FontWeight::Bold),
                                                TextEntry::make('stock_quantity')
                                                    ->label('Stock')
                                                    ->badge()
                                                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                                            ]),
                                    ]),
                                ]),

                            Tabs::make('Channel Information')
                                ->tabs([
                                    Tab::make('Current Channel')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                TextEntry::make('name')
                                                    ->label('Product Name')
                                                    ->weight(FontWeight::Bold)
                                                    ->size(TextEntry\TextEntrySize::Large),
                                                TextEntry::make('price')
                                                    ->money('USD')
                                                    ->weight(FontWeight::Bold)
                                                    ->color('success'),
                                                TextEntry::make('channel.name')
                                                    ->label('Channel')
                                                    ->badge(),
                                                // Add more channel-specific fields
                                            ]),
                                        ]),
                                    
                                    Tab::make('Other Channels')
                                        ->schema([
                                            RepeatableEntry::make('relatedProductsBySku')
                                                ->schema([
                                                    Grid::make(3)->schema([
                                                        TextEntry::make('name')
                                                            ->label('Product Name')
                                                            ->weight(FontWeight::Medium),
                                                        TextEntry::make('channel.name')
                                                            ->label('Channel')
                                                            ->badge(),
                                                        TextEntry::make('price')
                                                            ->money('USD'),
                                                    ]),
                                                ])
                                                ->columnSpanFull(),
                                        ]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(2),

                    Grid::make()
                        ->schema([
                            Section::make('SKU Information')
                                ->description('Product variations across all channels')
                                ->schema([
                                    TextEntry::make('grouped_related_products')
                                        ->label('Related SKUs')
                                        ->listWithLineBreaks()
                                        ->getStateUsing(function ($record) {
                                            return $record->getGroupedRelatedProducts()
                                                ->map(function ($products, $channelName) {
                                                    $productsInfo = $products->map(function ($product) {
                                                        return "SKU: {$product['sku']} - {$product['name']} (\${$product['price']})";
                                                    })->join("\n");
                                                    return "Channel: {$channelName}\n{$productsInfo}";
                                                })
                                                ->join("\n\n");
                                        }),
                                ]),

                            Section::make('Additional Images')
                                ->collapsible()
                                ->schema([
                                    RepeatableEntry::make('images')
                                        ->schema([
                                            ImageEntry::make('url')
                                                ->height(100)
                                                ->extraAttributes(['class' => 'rounded-lg']),
                                        ])
                                        ->columns(2),
                                ]),
                        ])
                        ->columnSpan(1),
                ]),
            ]);
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