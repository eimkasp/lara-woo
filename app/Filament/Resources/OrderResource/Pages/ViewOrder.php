<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Widgets\OrderSummaryWidget;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OrderSummaryWidget::class,
        ];
    }
}
