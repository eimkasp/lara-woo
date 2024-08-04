<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;

class CustomerOrders extends Widget
{
    public $customer;

    protected function getViewData(): array
    {
        $orders = Order::where('customer_id', $this->customer->id)
                       ->latest('created_at')
                       ->get();

        return [
            'orders' => $orders,
        ];
    }

    public function customer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    protected static string $view = 'filament.resources.customer-resource.widgets.customer-orders';
}
