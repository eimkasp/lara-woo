<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Channel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersPerDayChart extends ChartWidget
{
    protected static ?string $heading = 'Orders per Day';
    protected static ?int $sort = 1;
    // Make it span 2 columents
    protected int | string | array $columnSpan = 'full';
    public int $period = 30;

    // Make the chart reactive
    protected int $filterPeriod = 30;

    // Add polling to keep chart updated
    protected static ?string $pollingInterval = '10s';

    // Add chart height
    protected static ?string $contentHeight = '400px';

    protected function getFilters(): ?array
    {
        return [
            7 => '7 days',
            30 => '30 days',
            60 => '60 days',
             90 => '90 days',
            365 => '1 year',
        ];
    }

    public function filterChart(int $period): void
    {
        $this->filterPeriod = $period;
        $this->period = $period;
    }

    protected function getHeaderWidgets(): array
    {
        $currentPeriodOrders = Order::where('original_order_date', '>=', now()->subDays($this->filterPeriod))->count();
        $previousPeriodOrders = Order::where('original_order_date', '>=', now()->subDays($this->filterPeriod * 2))
            ->where('original_order_date', '<', now()->subDays($this->filterPeriod))
            ->count();
        
        $trend = $previousPeriodOrders > 0 
            ? (($currentPeriodOrders - $previousPeriodOrders) / $previousPeriodOrders) * 100 
            : 0;

        return [
            "<div class='flex items-center justify-between p-4 bg-white rounded-lg shadow'>
                <div>
                    <h3 class='text-lg font-medium text-gray-900'>Total Orders</h3>
                    <p class='text-3xl font-bold'>{$currentPeriodOrders}</p>
                </div>
                <div class='text-right'>
                    <p class='text-sm text-gray-600'>vs previous {$this->filterPeriod} days</p>
                    <p class='text-lg font-semibold " . ($trend >= 0 ? 'text-green-600' : 'text-red-600') . "'>
                        " . ($trend >= 0 ? '+' : '') . number_format($trend, 1) . "%
                    </p>
                </div>
            </div>"
        ];
    }

    protected function getData(): array
    {
        $days = $this->filterPeriod;
        $channels = Channel::all();
        $datasets = [];
        
        // Get total orders per day (for bar chart)
        $totalOrders = Order::selectRaw('DATE(original_order_date) as date, COUNT(*) as count')
            ->where('original_order_date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = collect(range($days - 1, 0))
            ->map(fn ($daysAgo) => now()->subDays($daysAgo)->format('Y-m-d'))
            ->flip()
            ->map(fn () => 0)
            ->merge($totalOrders->pluck('count', 'date'))
            ->sortKeys();

        // Add total orders bar chart
        $datasets[] = [
            'type' => 'bar',
            'label' => 'Total Orders',
            'data' => $dates->values()->toArray(),
            'backgroundColor' => 'rgba(156, 163, 175, 0.5)', // Gray
            'order' => 1,
            'yAxisID' => 'y',
        ];

        // Add line charts for each channel
        foreach ($channels as $channel) {
            $orders = Order::selectRaw('DATE(original_order_date) as date, COUNT(*) as count')
                ->where('channel_id', $channel->id)
                ->where('original_order_date', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $channelDates = collect(range($days - 1, 0))
                ->map(fn ($daysAgo) => now()->subDays($daysAgo)->format('Y-m-d'))
                ->flip()
                ->map(fn () => 0)
                ->merge($orders->pluck('count', 'date'))
                ->sortKeys();

            $color = $this->getChannelColor($channel->id);

            $datasets[] = [
                'type' => 'line',
                'label' => $channel->name,
                'data' => $channelDates->values()->toArray(),
                'fill' => false,
                'borderColor' => $color,
                'backgroundColor' => $color,
                'tension' => 0.3,
                'order' => 2,
                'yAxisID' => 'y1',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $dates->keys()->map(fn ($date) => Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'zoom' => [
                    'zoom' => [
                        'wheel' => [
                            'enabled' => true,
                        ],
                        'pinch' => [
                            'enabled' => true,
                        ],
                        'mode' => 'x',
                    ],
                    'pan' => [
                        'enabled' => true,
                        'mode' => 'x',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'animation' => [
                'duration' => 300,
            ],
        ];
    }

    // Helper function to generate consistent colors for channels
    protected function getChannelColor(int $channelId): string
    {
        // Predefined colors for better visual distinction
        $colors = [
            'rgb(59, 130, 246)', // Blue
            'rgb(220, 38, 38)',  // Red
            'rgb(16, 185, 129)', // Green
            'rgb(217, 119, 6)',  // Orange
            'rgb(139, 92, 246)', // Purple
            'rgb(236, 72, 153)', // Pink
        ];

        return $colors[$channelId % count($colors)];
    }

    protected function getType(): string
    {
        return 'mixed'; // Enable mixed chart types
    }
}
