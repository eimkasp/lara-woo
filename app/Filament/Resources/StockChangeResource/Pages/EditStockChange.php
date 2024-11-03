<?php

namespace App\Filament\Resources\StockChangeResource\Pages;

use App\Filament\Resources\StockChangeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockChange extends EditRecord
{
    protected static string $resource = StockChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
