<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockChangeResource\Pages;
use App\Models\StockChange;
use App\Models\Product;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Colors\Color;

class StockChangeResource extends Resource
{
    protected static ?string $model = StockChange::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Stock Changes';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(Product::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('quantity_change')
                            ->required()
                            ->integer()
                            ->label('Quantity Change')
                            ->suffix('units')
                            ->helperText('Enter positive numbers for stock addition, negative for reduction')
                            ->columnSpan(1),
                        Forms\Components\Select::make('source')
                            ->required()
                            ->options([
                                'manual' => 'Manual Adjustment',
                                'webhook' => 'WooCommerce Webhook',
                                'sync' => 'Sync Process',
                            ])
                            ->default('manual')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('quantity_change')
                    ->label('Stock Change')
                    ->sortable()
                    ->alignment('center')
                    ->icon(fn ($record) => $record->quantity_change > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                    ->iconColor(fn ($record) => $record->quantity_change > 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . number_format($state)),
                Tables\Columns\TextColumn::make('product.stock_quantity')
                    ->label('Current Stock')
                    ->sortable()
                    ->alignment('center'),
                Tables\Columns\BadgeColumn::make('source')
                    ->colors([
                        'warning' => 'manual',
                        'success' => 'webhook',
                        'primary' => 'sync',
                    ])
                    ->icons([
                        'heroicon-o-pencil' => 'manual',
                        'heroicon-o-globe-alt' => 'webhook',
                        'heroicon-o-arrow-path' => 'sync',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'manual' => 'Manual Adjustment',
                        'webhook' => 'WooCommerce Webhook',
                        'sync' => 'Sync Process',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->whereDate('created_at', '<=', $data['to']));
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Stock Change')
                    ->icon('heroicon-o-plus'),
            ])
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockChanges::route('/'),
            'create' => Pages\CreateStockChange::route('/create'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }
}
