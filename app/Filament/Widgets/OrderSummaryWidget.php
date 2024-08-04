<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class OrderSummaryWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $totalRevenue = cache()->remember('total_revenue', 60, function () {
            return Order::sum('total');
        });

        $totalOrders = cache()->remember('total_orders', 60, function () {
            return Order::count();
        });

        $totalItemsSold = cache()->remember('total_items_sold', 60, function () {
            return Order::with('products')->get()->sum(function ($order) {
                return $order->products->sum('pivot.quantity');
            });
        });

        return [
            Card::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('Total revenue from all orders')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('success'),
            
            Card::make('Total Orders', $totalOrders)
                ->description('Total number of orders')
                ->descriptionIcon('heroicon-s-shopping-cart')
                ->color('primary'),
            
            Card::make('Total Items Sold', $totalItemsSold)
                ->description('Total quantity of items sold')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('warning'),
        ];
    }
}
