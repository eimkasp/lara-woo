<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Widgets\OrderSummaryWidget;
use App\Models\Order;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;


class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
    
    protected function getHeaderWidgets(): array
    {
        return [
            OrderSummaryWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
            ->badge(Order::query()->count()),
            'processing' => Tab::make('Processing')
            ->badge(Order::query()->where('status', 'processing')->count())
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'processing')),
            'completed' => Tab::make('Completed')
                ->badge(Order::query()->where('status', 'completed')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed')),
               
            'refunded' => Tab::make()
                ->badge(Order::query()->where('status', 'refunded')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'refunded')),
                'cancelled' => Tab::make()
                ->badge(Order::query()->where('status', 'cancelled')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
