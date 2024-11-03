<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Spatie\Activitylog\Models\Activity;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getAdditionalInformationSchema(): array
    {
        return [
            Section::make('Activity Log')
                ->description('Recent changes to this product')
                ->schema([
                    TextEntry::make('activities')
                        ->listWithLineBreaks()
                        ->state(
                            Activity::where('subject_type', Product::class)
                                ->where('subject_id', $this->record->id)
                                ->orderBy('created_at', 'desc')
                                ->get()
                                ->map(fn ($activity) => 
                                    "[{$activity->created_at->diffForHumans()}] {$activity->description}"
                                )
                                ->toArray()
                        ),
                ]),
        ];
    }
}