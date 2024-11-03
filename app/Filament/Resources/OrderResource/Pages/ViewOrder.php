<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Spatie\Activitylog\Models\Activity;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return "Order #{$this->record->id}";
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)->schema([
                    // Left Column (2/3 width)
                    Grid::make()
                        ->columnSpan(2)
                        ->schema([
                            // Order Summary Section
                            Section::make('Order Summary')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('id')
                                            ->label('Order ID')
                                            ->weight(FontWeight::Bold),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'cancelled' => 'danger',
                                                default => 'secondary',
                                            }),
                                        TextEntry::make('total')
                                            ->label('Order Total')
                                            ->money('USD')
                                            ->weight(FontWeight::Bold)
                                            ->color('success'),
                                        TextEntry::make('original_order_date')
                                            ->label('Order Date')
                                            ->dateTime(),
                                        TextEntry::make('channel.name')
                                            ->label('Sales Channel')
                                            ->badge(),
                                        TextEntry::make('woocommerce_id')
                                            ->label('WooCommerce ID'),
                                    ]),
                                ]),

                            // Order Items Section
                            Section::make('Order Items')
                                ->schema([
                                    RepeatableEntry::make('products')
                                        ->schema([
                                            Grid::make(4)->schema([
                                                TextEntry::make('name')
                                                    ->label('Product'),
                                                TextEntry::make('sku')
                                                    ->label('SKU'),
                                                TextEntry::make('pivot.quantity')
                                                    ->label('Quantity'),
                                                TextEntry::make('price')
                                                    ->label('Unit Price')
                                                    ->money('USD'),
                                            ]),
                                        ]),
                                ]),
                        ]),

                    // Right Column (1/3 width)
                    Grid::make()
                        ->columnSpan(1)
                        ->schema([
                            // Customer Information Section
                            Section::make('Customer Information')
                                ->schema([
                                    TextEntry::make('customer.name')
                                        ->label('Customer Name'),
                                    TextEntry::make('customer.email')
                                        ->label('Email')
                                        ->copyable(),
                                    TextEntry::make('customer.phone')
                                        ->label('Phone')
                                        ->copyable(),
                                ]),

                            // Contact & Delivery Information Section
                            Section::make('Contact & Delivery Information')
                                ->schema([
                                    Tabs::make('Address Information')
                                        ->tabs([
                                            Tab::make('Billing 🧾')
                                                ->schema([
                                                    Grid::make(1)->schema([
                                                        TextEntry::make('billing_full_name')
                                                            ->label('Billing Contact')
                                                            ->getStateUsing(fn ($record) => 
                                                                "{$record->billing_first_name} {$record->billing_last_name}"
                                                            ),
                                                        TextEntry::make('billing_email')
                                                            ->label('Email')
                                                            ->copyable(),
                                                        TextEntry::make('billing_phone')
                                                            ->label('Phone')
                                                            ->copyable(),
                                                        TextEntry::make('billing_address')
                                                            ->label('Address')
                                                            ->getStateUsing(function ($record) {
                                                                return implode("\n", array_filter([
                                                                    $record->billing_address_1,
                                                                    $record->billing_address_2,
                                                                    $record->billing_city,
                                                                    $record->billing_state . ' ' . $record->billing_postcode,
                                                                    $record->billing_country,
                                                                ]));
                                                            })
                                                            ->markdown(),
                                                    ]),
                                                ]),
                                            Tab::make('Shipping 📦')
                                                ->schema([
                                                    Grid::make(1)->schema([
                                                        TextEntry::make('shipping_method')
                                                            ->label('Shipping Method'),
                                                        TextEntry::make('shipping_total')
                                                            ->label('Shipping Cost')
                                                            ->money('USD'),
                                                        TextEntry::make('shipping_contact')
                                                            ->label('Ship To')
                                                            ->getStateUsing(fn ($record) => 
                                                                "{$record->shipping_first_name} {$record->shipping_last_name}"
                                                            ),
                                                        TextEntry::make('shipping_address')
                                                            ->label('Address')
                                                            ->getStateUsing(function ($record) {
                                                                return implode("\n", array_filter([
                                                                    $record->shipping_address_1,
                                                                    $record->shipping_address_2,
                                                                    $record->shipping_city,
                                                                    $record->shipping_state . ' ' . $record->shipping_postcode,
                                                                    $record->shipping_country,
                                                                ]));
                                                            })
                                                            ->markdown(),
                                                    ]),
                                                ]),
                                        ]),
                                ]),
                        ]),
                ]),

                // Activity Log Section (Full Width)
                Section::make('Activity Log')
                    ->collapsible()
                    ->description('Recent changes to this order')
                    ->schema([
                        TextEntry::make('activities')
                            ->listWithLineBreaks()
                            ->state(
                                Activity::where('subject_type', Order::class)
                                    ->where('subject_id', $this->record->id)
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(fn ($activity) => 
                                        "[{$activity->created_at->diffForHumans()}] {$activity->description}"
                                    )
                                    ->toArray()
                            ),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
