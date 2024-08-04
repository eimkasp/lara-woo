<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class LatestCustomers extends BaseWidget
{
    protected static ?string $heading = 'Top Customers';

    /**
     * Get the query used to retrieve the top customers.
     *
     * @return Builder|null
     */
    protected function getTableQuery(): Builder
    {
        return Customer::query()
            ->select('customers.*')
            ->join('orders', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('SUM(orders.total) as total_spent, MAX(orders.created_at) as last_order, COUNT(orders.id) as number_of_orders')
            ->groupBy('customers.id')
            ->orderByDesc('total_spent')
            ->limit(5); // Adjust the limit as needed
    }

    /**
     * Get the columns for the table.
     *
     * @return array
     */
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('email')->label('Email')->sortable(),
            Tables\Columns\TextColumn::make('total_spent')->label('Total Spent')->sortable(),
            Tables\Columns\TextColumn::make('last_order')->label('Last Order')->sortable()->since(),
            Tables\Columns\TextColumn::make('number_of_orders')->label('Number of Orders')->sortable(),
        ];
    }
}
