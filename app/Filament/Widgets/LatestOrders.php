<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    protected static ?string $heading = 'Latest Orders';

    /**
     * Get the query used to retrieve the latest orders.
     *
     * @return Builder|null
     */
    protected function getTableQuery(): Builder
    {
        return Order::query()->latest()->limit(5); // Adjust the limit as needed
    }

    /**
     * Get the columns for the table.
     *
     * @return array
     */
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')->label('Time Ago')->sortable()->since(),
            Tables\Columns\BadgeColumn::make('status')->label('Status')->sortable()->colors([
                'pending' => 'yellow',
                'completed' => 'green',
                'canceled' => 'red',
                'refunded' => 'red',

            ]),
            Tables\Columns\TextColumn::make('total')->label('Total')->sortable(),
            Tables\Columns\TextColumn::make('customer.email')->label('Customer Email')->sortable(),
            Tables\Columns\TextColumn::make('id')->label('Order ID')->sortable(),
        ];
    }
}
