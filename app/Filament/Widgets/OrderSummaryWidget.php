<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class OrderSummaryWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $totalRevenue = Order::sum('total');
        $totalOrders = Order::count();
        $totalItemsSold = Order::with('products')->get()->sum(function ($order) {
            return $order->products->sum('pivot.quantity');
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
