<?php

namespace App\Filament\Resources\ChannelResource\RelationManagers;

use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected function getTableQuery(): Builder
    {
        return Customer::query()
            ->select('customers.*')
            ->join('orders', 'customers.id', '=', 'orders.customer_id')
            ->selectRaw('SUM(orders.total) as total_spent, MAX(orders.created_at) as last_order, COUNT(orders.id) as number_of_orders')
            ->groupBy('customers.id')
            ->orderByDesc('total_spent')
            ->limit(5); // Adjust the limit as needed
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('total_spent'),
                Tables\Columns\TextColumn::make('number_of_orders'),
                // add order count
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
