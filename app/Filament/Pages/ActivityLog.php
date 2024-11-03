<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Spatie\Activitylog\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ActivityLog extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 100;

    // Add pooling 
    protected static ?int $pooling = 3;
    protected static ?string $title = 'Activity Log';

    public function getViewData(): array
    {
        return [
            'overview' => $this->getOverview(),
        ];
    }

    private function getOverview(): array
    {
        return [
            'last_hour' => [
                'total' => $this->getActivityCount(Carbon::now()->subHour()),
                'products' => $this->getActivityCount(Carbon::now()->subHour(), 'products'),
            ],
            'last_day' => [
                'total' => $this->getActivityCount(Carbon::now()->subDay()),
                'products' => $this->getActivityCount(Carbon::now()->subDay(), 'products'),
            ],
            'last_month' => [
                'total' => $this->getActivityCount(Carbon::now()->subMonth()),
                'products' => $this->getActivityCount(Carbon::now()->subMonth(), 'products'),
            ],
        ];
    }

    private function getActivityCount(Carbon $since, ?string $logName = null): int
    {
        $query = Activity::where('created_at', '>=', $since);
        
        if ($logName) {
            $query->where('log_name', $logName);
        }
        
        return $query->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with(['subject.channel']) // Eager load relationships
            )
            ->columns([
                IconColumn::make('event')
                    ->icon(fn (string $state): string => match ($state) {
                        'created' => 'heroicon-o-plus-circle',
                        'updated' => 'heroicon-o-pencil',
                        'deleted' => 'heroicon-o-trash',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('log_name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'products' => 'success',
                        'orders' => 'warning',
                        'customers' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->searchable()
                    ->formatStateUsing(fn($state) => str_replace('App\\Models\\', '', $state)),
                TextColumn::make('subject_details')
                    ->label('Subject')
                    ->getStateUsing(function ($record) {
                        if (!$record->subject) {
                            return 'Deleted record';
                        }

                        $details = match ($record->subject_type) {
                            'App\\Models\\Product' => "[{$record->subject->sku}] {$record->subject->name}",
                            'App\\Models\\Order' => "Order #{$record->subject->id}",
                            'App\\Models\\Customer' => $record->subject->email,
                            default => $record->subject->id,
                        };

                        if (method_exists($record->subject, 'channel') && $record->subject->channel_id) {
                            $details .= " ({$record->subject->channel_id})";
                        }

                        return $details;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('subject', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('sku', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap()
                    ->html(),
                TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default('System'),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state->diffForHumans()),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->multiple()
                    ->options([
                        'products' => 'Products',
                        'orders' => 'Orders',
                        'customers' => 'Customers',
                    ]),
                SelectFilter::make('event')
                    ->multiple()
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
            ])
            ->striped();
    }

    protected static string $view = 'filament.pages.activity-log';
}
