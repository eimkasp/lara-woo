<?php 

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\RelationManagers\CustomerRelationManager;
use Filament\Forms;
use Filament\Tables;
use App\Models\Channel;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Console\Commands\SyncWooCommerceData;
use App\Jobs\SyncProductsJob;
use App\Jobs\SyncOrdersJob;
use App\Jobs\SyncCustomersJob;
use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers\ProductRelationManager;
use App\Filament\Resources\ChannelResource\RelationManagers\OrderChannelRelationManager;
use Filament\Tables\Actions\Action;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-wifi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_url')
                    ->required()
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('consumer_key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('consumer_secret')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('base_url')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customers_count')
                    ->label('Customers')
                    ->counts('customers')
                    ->sortable(),
            ])
            ->actions([
                Action::make('sync_products')
                    ->label('Sync Products')
                    ->action(function (Channel $record) {
                        SyncProductsJob::dispatch($record);
                    })
                    ->requiresConfirmation()
                    ->color('primary'),

                Action::make('sync_orders')
                    ->label('Sync Orders')
                    ->action(function (Channel $record) {
                        SyncOrdersJob::dispatch($record);
                    })
                    ->requiresConfirmation()
                    ->color('primary'),

                Action::make('sync_customers')
                    ->label('Sync Customers')
                    ->action(function (Channel $record) {
                        SyncCustomersJob::dispatch($record);
                    })
                    ->requiresConfirmation()
                    ->color('primary'),
            ])
            ->filters([
                // Add any filters you need here
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
            'view' => Pages\ViewChannel::route('/{record}'), // Register the view page
        ];
    }

    public static function getRelations(): array
    {
        return [
            ProductRelationManager::class, // Register the ProductRelationManager
            OrderChannelRelationManager::class, // Register the OrderRelationManager
            \App\Filament\Resources\ChannelResource\RelationManagers\CustomerRelationManager::class, // Register the CustomerRelationManager
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Show the count of channels or any other relevant badge
        return (string) Channel::count();
    }
}

