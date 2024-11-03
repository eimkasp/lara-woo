<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class OrdersByStatusWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = '2';
    protected static ?int $columns = 2;

    protected function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    protected function getCards(): array
    {
        $statuses = Cache::remember('orders_by_status', now()->addMinutes(10), function () {
            return Order::select('status')
                ->selectRaw('count(*) as count')
                ->groupBy('status')
                ->get();
        });

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
