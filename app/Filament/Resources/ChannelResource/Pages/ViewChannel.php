<?php

namespace App\Filament\Resources\ChannelResource\Pages;

use App\Filament\Resources\ChannelResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;
use Filament\Widgets\StatsOverviewWidget;


class ViewChannel extends ViewRecord
{
    protected static string $resource = ChannelResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('sync')
                ->label('Sync All')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    \Artisan::call('sync:woocommerce', [
                        '--channel' => $record->id,
                    ]);
                }),
            Actions\Action::make('syncProducts')
                ->label('Sync Products')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    \App\Jobs\SyncProductsJob::dispatch($record);
                }),
            Actions\Action::make('syncCustomers')
                ->label('Sync Customers')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;
                    \App\Jobs\SyncCustomersJob::dispatch($record);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
                  
        ];
    }
    
    
}