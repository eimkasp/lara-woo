<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use App\Filament\Resources\ChannelResource\Pages;
use App\Models\Channel;
use Illuminate\Support\HtmlString;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function getNavigationBadge(): ?string
    {
        return new HtmlString('🟢 0 Issues');


    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_url')
                    ->required()
                    ->url(),
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
                Tables\Columns\TextColumn::make('customers_count')
                    ->label('Customers')
                    ->counts('customers')
                    ->sortable(),

                // Column to show the number of products related to this channel
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),


                // Column to show the number of orders related to this channel
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable(),
                // Add columns for last sync time and status
                Tables\Columns\TextColumn::make('meta.last_sync_time')
                    ->label('Last Sync Time')
                    ->getStateUsing(fn(Channel $record) => $record->meta->where('last_sync_time')->last() ? $record->meta->where('last_sync_time')->last() : '🕒 Never')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('meta.last_sync_status')
                    ->label('Last Sync Status')
                    ->colors([
                        'success' => 'success',
                        'danger' => 'failed',
                    ])
                    ->getStateUsing(fn(Channel $record) => $record->meta->where('last_sync_status')->last() ? $record->meta->where('last_sync_status')->last() : '🔴 Never'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (Channel $record) {
                        // Dispatch the sync command for this specific customer or related channel
                        \Artisan::call('sync:woocommerce', [
                            '--channel' => $record->id,
                        ]);

                        // Optionally, provide user feedback
                        $this->notify('success', 'Sync started for ' . $record->name);
                    }),
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
        ];
    }
}

