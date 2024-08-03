<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\OrderSummaryWidget;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class CustomDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home'; // Optional: Set an icon for the dashboard
    protected static string $view = 'filament.pages.custom-dashboard'; // Points to the Blade view, if custom

    protected function getWidgets(): array
    {
        return [
            OrderSummaryWidget::class, // Your custom widget as the first widget
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }
}
