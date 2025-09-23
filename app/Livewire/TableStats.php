<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Laporan;
use Livewire\WithPagination;

class TableStats extends Component
{
    use WithPagination;

    protected string $pageName = 'laporanPage';

    public bool $readyToLoad = false;
    public int $perPage = 5;
    public string $search = '';

    public function submitSearch(): void
    {
        $this->resetPage($this->pageName);
    }

    public function updatedPerPage()
    {
        $this->resetPage($this->pageName);
    }

    public function updatedSearch()
    {
        $this->resetPage($this->pageName);
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
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);

                $q->where(function ($q) use ($term) {
                    $q->whereHas('mesins', function ($mq) use ($term) {
                        $mq->where('nama_mesin', 'like', "%{$term}%")
                            ->orWhere('nama_plant', 'like', "%{$term}%");
                    })
                        ->orWhereHas('karyawans', function ($kq) use ($term) {
                            $kq->where('nik', 'like', "%{$term}%")
                                ->orWhere('nama', 'like', "%{$term}%");
                        })
                        ->orWhere('detail_produksi', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage, ['*'], $this->pageName)
            : collect();

        return view('livewire.table-stats', [
            'laporan' => $laporan,
            'total'   => $total,
        ]);
    }
}
