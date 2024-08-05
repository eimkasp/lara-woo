<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\ProductSalesRevenueWidget;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets\StatsOverviewWidget;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // ProductSalesRevenueWidget::class, // Add the custom widget to the page
        ];
    }

    
}