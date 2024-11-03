<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Channel;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All Customers')
                ->badge(static::getModel()::count()),
        ];

        // Add tab for each channel
        Channel::all()->each(function ($channel) use (&$tabs) {
            $tabs[$channel->slug] = Tab::make($channel->name)
                ->badge($channel->customers()->count())
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('channel_id', $channel->id)
                );
        });

         // Add tab for customers with duplicate emails
        $tabs['duplicates'] = Tab::make('Loyal Customers')
            ->badge(static::getModel()::withDuplicateEmails()->count())
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->withDuplicateEmails()
            );

        return $tabs;
    }

    public function getDefaultTableSortColumn(): ?string
    {
        return 'total_spent';
    }

    public function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableFilters(): array
    {
        return [
            ...parent::getTableFilters(),
            \Filament\Tables\Filters\SelectFilter::make('channel_id')
                ->label('Channel')
                ->options(Channel::pluck('name', 'id')),
            \Filament\Tables\Filters\Filter::make('duplicate_email')
                ->label('Duplicate Emails Across Channels')
                ->query(fn (Builder $query) => $query->withDuplicateEmails())
        ];
    }
}
