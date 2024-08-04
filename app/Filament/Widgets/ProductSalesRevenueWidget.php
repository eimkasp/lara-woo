<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Product;

class ProductSalesRevenueWidget extends Widget
{
    // protected static string $view = 'filament.widgets.product-sales-revenue-widget';

    public Product $product;

    public function mount(Product $record): void
    {
        $this->product = $record;
    }

    protected function getViewData(): array
    {
        $totalSales = $this->product->orders()->sum('pivot.quantity');
        $totalRevenue = $this->product->orders()->sum('pivot.quantity * orders.total');
        return [
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
        ];
    }
}