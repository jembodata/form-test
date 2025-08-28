<?php

namespace App\Filament\Widgets;

use App\Models\Laporan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // protected function getHeading(): ?string
    // {
    //     return 'Summary';
    // }
    
    protected function getStats(): array
    {
        return [
            //
            // Stat::make('Kendala', Laporan::where('feedback', 1)->count())
            //     ->description('Jumlah laporan dengan kendala')
            //     ->color('danger'),
            // Stat::make('Tidak Ada Kendala', Laporan::where('feedback', 0)->count())
            //     ->description('Jumlah laporan tanpa kendala')
            //     ->color('primary'),
            Stat::make('Laporan', Laporan::count())
                ->description('Jumlah laporan')
                ->color('primary'),
            // Stat::make('Mesin', Laporan::distinct('mesin_id')->count())
            //     ->description('Jumlah mesin yang dilaporkan')
            //     ->color('success'),
            // Stat::make('Kendala', Laporan::whereNotNull('kendala')->count())
            //     ->description('Jumlah laporan dengan kendala')
            //     ->color('danger'),
        ];
    }
}
