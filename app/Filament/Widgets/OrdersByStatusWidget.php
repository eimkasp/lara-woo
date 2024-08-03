<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrdersByStatusWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $statuses = Order::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->get();

        return $statuses->map(function ($status) {
            return Card::make(ucfirst($status->status), $status->count)
                ->color($this->getStatusColor($status->status)); // Optional: set color based on status
        })->toArray();
    }

    protected function getStatusColor(string $status): string
    {
        // Customize colors based on your order status
        return match ($status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'canceled' => 'red',
            default => 'gray',
        };
    }
}
