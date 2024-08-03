<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Order;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerDetailsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.customer-details-page';

    public $customer;
    public $customerId;

    protected static ?string $navigationLabel = 'Customer Details';
    protected static ?string $navigationGroup = 'Customers';
    protected static ?string $slug = 'customers/{customerId}/details';

    public static function shouldRegisterNavigation(): bool
    {
        return true; // If you don't want this to appear in the main navigation menu
    }

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = Customer::find($customerId);

        if (!$this->customer) {
            throw new ModelNotFoundException('Customer not found.');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            $this->getOrderStats(),
        ];
    }

    protected function getOrderStats(): Card
    {
        $totalOrders = Order::where('customer_id', $this->customerId)->count();
        $totalSpent = Order::where('customer_id', $this->customerId)->sum('total');
        $avgOrderValue = Order::where('customer_id', $this->customerId)->average('total');

        return Card::make('Order Statistics', '')
            ->description("Total Orders: $totalOrders")
            ->descriptionIcon('heroicon-o-clipboard-list')
            ->extraAttributes([
                'Total Spent: ' => money_format('$%i', $totalSpent),
                'Average Order Value: ' => money_format('$%i', $avgOrderValue),
            ]);
    }
}
