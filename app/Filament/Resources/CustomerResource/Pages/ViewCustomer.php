<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Order;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function getOrders()
    {
        return Order::where('customer_id', $this->record->id)->get();
    }
    
}
