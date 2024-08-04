<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\OrderSummaryWidget;
use App\Filament\Widgets\CustomerSummaryWidget;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            OrderSummaryWidget::class,
            CustomerSummaryWidget::class,
        ];
    }

    public function getColumns(): int
    {
        return 1; // Define the number of columns
    }

    protected function getWidgetsLayout(): array
    {
        return [
            'columns' => [
                [
                    'width' => 1,
                    'widgets' => [
                        StatsOverviewWidget::class,
                    ],
                ],
                [
                    'width' => 1,
                    'widgets' => [
                        OrderSummaryWidget::class,
                        CustomerSummaryWidget::class,
                    ],
                ],
            ],
        ];
    }
}