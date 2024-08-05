<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use App\Models\Product;

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
        return Product::query()->with('variations');
    }
    public static function getNavigationBadge(): ?string
    {
        // Show out of stock products count 
        return 'Out of stock: ' . (string) Product::whereHas('variations', function ($query) {
            $query->where('stock_quantity', 0);
        })->count();
    }
    

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Product Name')
                ->sortable(),

            Tables\Columns\Layout\Panel::make([
                Stack::make([
                    TextColumn::make('variation_name')
                        ->label('Variation Name')
                        ->getStateUsing(function ($record) {
                            return $record->variations->pluck('name')->implode(', ');
                        }),

                    TextColumn::make('variation_stock_quantity')
                        ->label('Stock Quantity')
                        ->getStateUsing(function ($record) {
                            return $record->variations->pluck('stock_quantity')->implode(', ');
                        }),
                ]),
            ]),
        ];
    }
}

