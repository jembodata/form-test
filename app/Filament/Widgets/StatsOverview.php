<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LaporanResource;
use App\Models\Laporan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;


class StatsOverview extends BaseWidget
{
    // protected function getHeading(): ?string
    // {
    //     return 'Summary';
    // }

    use InteractsWithPageTable;

    // protected static bool $isLazy = false;
    // protected static ?string $pollingInterval = null;

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

            Stat::make('Shift 1', Laporan::where('shift', 1)->count())
                ->description('Jumlah laporan shift 1')
                ->color('info'),

            Stat::make('Shift 2', Laporan::where('shift', 2)->count())
                ->description('Jumlah laporan shift 2')
                ->color('info'),

            Stat::make('Shift 3', Laporan::where('shift', 3)->count())
                ->description('Jumlah laporan shift 3')
                ->color('info'),

            // Stat::make('Total orders', $this->getPageTableQuery()->count()),
            // Stat::make('Mesin', Laporan::distinct('mesin_id')->count())
            //     ->description('Jumlah mesin yang dilaporkan')
            //     ->color('success'),
            // Stat::make('Kendala', Laporan::whereNotNull('kendala')->count())
            //     ->description('Jumlah laporan dengan kendala')
            //     ->color('danger'),
        ];
    }
}
