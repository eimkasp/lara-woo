<?php

namespace App\Filament\Resources;

use App\Filament\Pages\CustomerDetailsPage;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\OrderRelationManager;
use App\Models\Customer;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function getNavigationBadge(): ?string
    {
        return (string) Customer::count(); // Example: showing total order count as a badge
    }
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                Forms\Components\Select::make('channel_id')
                    ->relationship('channel', 'name')
                    ->required(),
                // Forms\Components\HasManyRepeater::make('orders')
                //     ->relationship('orders')
                //     ->schema([
                //         Forms\Components\TextInput::make('id')->disabled(),
                //         Forms\Components\TextInput::make('status')->required(),
                //         Forms\Components\TextInput::make('total')->required(),
                //     ])
                //     ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('last_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')  // Counting the number of related orders
                    ->sortable(),
            ])
            ->filters([
                // Add any filters you need here
            ]);
          
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            OrderRelationManager::class,
        ];
    }
}