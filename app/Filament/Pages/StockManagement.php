<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StockStats;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Product;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Infolists\Components\Grid;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Components\Tabs; // Add this import
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class StockManagement extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.stock-management';

    protected static ?string $title = 'Stock report';

    // add icon
    protected static ?string $description = 'View and update stock';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected function getTableQuery()
    {
        return Product::query()
            ->with(['variations', 'channel', 'relatedProductsBySku'])
            ->withCount('variations')
            ->distinct('sku');
    }

    public static function getNavigationBadge(): ?string
    {
        // Show out of stock products count 
        return 'Out of stock: ' . (string) Product::whereHas('variations', function ($query) {
            $query->where('stock_quantity', 0);
        })->count();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // StockStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Products')
                ->icon('heroicon-o-squares-2x2')
                ->badge(Product::query()->count()),
            
            'out_of_stock' => Tab::make('Out of Stock')
                ->icon('heroicon-o-exclamation-circle')
                ->badge(Product::whereHas('variations', fn($q) => $q->where('stock_quantity', 0))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('variations', fn($q) => $q->where('stock_quantity', 0))),
            
            'low_stock' => Tab::make('Low Stock')
                ->icon('heroicon-o-exclamation-triangle')
                ->badge(Product::whereHas('variations', fn($q) => $q->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('variations', fn($q) => $q->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5))),
            
            'in_stock' => Tab::make('In Stock')
                ->icon('heroicon-o-check-circle')
                ->badge(Product::whereHas('variations', fn($q) => $q->where('stock_quantity', '>=', 5))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('variations', fn($q) => $q->where('stock_quantity', '>=', 5))),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->columns([
                Split::make([
                    TextColumn::make('sku')
                        ->label('SKU')
                        ->searchable()
                        ->sortable()
                        ->weight('bold'),
                    
                    TextColumn::make('name')
                        ->label('Product Name')
                        ->searchable()
                        ->sortable(),

                    BadgeColumn::make('overall_stock_status')
                        ->getStateUsing(function ($record) {
                            $totalStock = $record->relatedProductsBySku
                                ->flatMap->variations
                                ->sum('stock_quantity');
                            
                            return $this->getStockStatus($totalStock);
                        })
                        ->colors([
                            'danger' => 'Out of Stock',
                            'warning' => 'Low Stock',
                            'success' => 'In Stock',
                        ]),
                ]),
                
                Panel::make([
                    View::make('filament.pages.stock-management.channel-stock-details')
                        ->collapsible()
                        ->collapsed()
                ])->collapsible(),
            ])
            ->defaultSort('sku', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('has_variations')
                    ->label('Product Type')
                    ->placeholder('All Products')
                    ->trueLabel('Variable Products')
                    ->falseLabel('Simple Products')
                    ->queries(
                        true: fn ($query) => $query->has('variations'),
                        false: fn ($query) => $query->doesntHave('variations'),
                        blank: fn ($query) => $query
                    ),
                Tables\Filters\SelectFilter::make('stock_status')
                    ->options([
                        'out_of_stock' => 'Out of Stock',
                        'low_stock' => 'Low Stock',
                        'in_stock' => 'In Stock',
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'out_of_stock' => $query->whereHas('variations', fn ($q) => $q->where('stock_quantity', 0)),
                            'low_stock' => $query->whereHas('variations', fn ($q) => $q->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5)),
                            'in_stock' => $query->whereHas('variations', fn ($q) => $q->where('stock_quantity', '>=', 5)),
                        };
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3);
    }

    

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Product Name')
                ->searchable()
                ->sortable()
                ->weight('bold'),

            BadgeColumn::make('stock_status')
                ->label('Status')
                ->getStateUsing(fn ($record) => $this->getStockStatus($record))
                ->colors([
                    'danger' => 'Out of Stock',
                    'warning' => 'Low Stock',
                    'success' => 'In Stock',
                ])
                ->sortable(),

            Tables\Columns\Layout\Panel::make([
                Stack::make([
                    TextColumn::make('variation_details')
                        ->label('Variation Details')
                        ->getStateUsing(function ($record) {
                            return $record->variations->map(function ($variation) {
                                $status = $variation->stock_quantity <= 0 ? '🔴' : 
                                    ($variation->stock_quantity < 5 ? '🟡' : '🟢');
                                return "{$variation->name}: {$variation->stock_quantity} {$status}";
                            })->implode("\n");
                        })
                        ->html()
                        ->wrap(),

                    TextColumn::make('last_updated')
                        ->label('Last Stock Update')
                        ->getStateUsing(fn ($record) => $record->updated_at->diffForHumans())
                        ->icon('heroicon-m-clock')
                        ->color('gray'),
                ]),
            ])->collapsible(),
        ];
    }

    private function getStockStatus($totalStock): string
    {
        if ($totalStock <= 0) {
            return 'Out of Stock';
        }
        if ($totalStock < 5) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('stock_status')
                ->options([
                    'out_of_stock' => 'Out of Stock',
                    'low_stock' => 'Low Stock',
                    'in_stock' => 'In Stock',
                ])
                ->query(function ($query, array $data) {
                    if (empty($data['value'])) {
                        return $query;
                    }

                    return match ($data['value']) {
                        'out_of_stock' => $query->whereHas('variations', fn ($q) => $q->where('stock_quantity', 0)),
                        'low_stock' => $query->whereHas('variations', fn ($q) => $q->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 5)),
                        'in_stock' => $query->whereHas('variations', fn ($q) => $q->where('stock_quantity', '>=', 5)),
                    };
                }),
        ];
    }
}

