<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Laporan;


class ReportStats extends Component
{

    public function getStatsProperty(): array
    {
        $s1 = Laporan::where('shift', 1)->count();
        $s2 = Laporan::where('shift', 2)->count();
        $s3 = Laporan::where('shift', 3)->count();
        $total = Laporan::count();

        return compact('s1', 's2', 's3', 'total');
    }

    public function render()
    {
        return view('livewire.report-stats');
    }
}
