<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\ChannelStock;
use Illuminate\Support\Facades\DB;

class StockStats extends BaseWidget
{
    protected function getStats(): array
    {
        $channelStats = ChannelStock::select('channel', 
            DB::raw('COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock'),
            DB::raw('COUNT(CASE WHEN quantity > 0 AND quantity < 5 THEN 1 END) as low_stock'),
            DB::raw('SUM(quantity) as total_stock')
        )
        ->groupBy('channel')
        ->get();

        $stats = [];
        
        foreach ($channelStats as $channelStat) {
            $stats[] = Stat::make("{$channelStat->channel} Stock", $channelStat->total_stock)
                ->description("Out: {$channelStat->out_of_stock} | Low: {$channelStat->low_stock}")
                ->color($channelStat->out_of_stock > 0 ? 'danger' : 'success')
                ->chart([random_int(60, 100), random_int(40, 80), random_int(20, 60)]);
        }

        return $stats;
    }
}