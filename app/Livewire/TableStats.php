<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Laporan;
use Livewire\WithPagination;

class TableStats extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;
    public int $perPage = 5;

    protected string $pageName = 'laporanPage';

    // protected $queryString = [
    //     'page' => ['except' => 1],
    // ];

    // reset page when perPage changes (if you later expose it)
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function load(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $total = Laporan::count();

        $laporan = $this->readyToLoad
            ? Laporan::with([
                'mesins:id,nama_plant,nama_mesin',
                'karyawans:id,nama,nik',
            ])
            ->orderByDesc('created_at')
            ->paginate($this->perPage)
            : collect();

        return view('livewire.table-stats', [
            'laporan' => $laporan,
            'total'   => $total,
        ]);
    }
}
