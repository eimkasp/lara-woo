<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Tables;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                        Placeholder::make('image_preview')
                        ->content(function (Get $get): HtmlString {
                            $url = $get('url'); // Fetch the URL from the form state
                            if ($url) {
                                return new HtmlString('<img src="' . e($url) . '" style="max-width: 200px;" />');
                            }

                            return new HtmlString('<p>No image available</p>');
                        }),
                        Forms\Components\TextInput::make('url')
                            ->label('Image URL')
                            ->disabled()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('image_preview', $state);  // Update the image preview state
                            }),
                        Forms\Components\Checkbox::make('is_primary')
                        ->disabled()
                            ->label('Primary Image'),
                       
                        // Forms\Components\View::make('filament.components.image-preview')
                        //     ->label('Image Preview')
                        //     ->visible(fn ($get) => $get('url') != null)  // Only visible if URL is set
                        //     ->extraAttributes([
                        //         'style' => 'max-width: 200px;',
                        //     ])
                        //     ->viewData([
                        //         // get image url string 
                        //         'imageUrl' => fn ($get) => $get('url'), 
                        //     ]),
                    ])
                    ->columns(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('sku')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')->sortable(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel') // Accessing the channel relationship directly
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('variations.sku')
                    ->label('Variation SKUs')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('images.url')
                    ->label('Image Preview') // Show image preview in the table
                    ->sortable()
                    ->disk('public') // Adjust to your file system disk if needed
                    ->width(100) // Adjust width as needed
                    ->height(100), // Adjust height as needed
            ])
            ->filters([
                // Add any filters you need here
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}