<?php

namespace App\Exports;

use App\Models\Laporan;
use App\Models\Mesin;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $Idmesin;
    protected $month;

    public function __construct($Idmesin, $month)
    {
        $this->Idmesin = $Idmesin;
        $this->month = $month;
    }

    public function collection()
    {
        $carbonMonth = Carbon::parse($this->month);

        $startOfMonth = $carbonMonth->copy()->startOfMonth()->startOfDay();
        $endOfMonth = $carbonMonth->copy()->endOfMonth()->endOfDay();

        return Laporan::where('mesin_id', $this->Idmesin)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->orderBy('shift', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Shift',
            'OP',
            'Customer',
            'Proses',
            'Type & Size',
            'Output [m]',
            'Hourmeter',
            'P [Jam]',
            'O [Jam]',
            'R [Jam]',
            'G [Jam]',
            'Kendala',
            'Keterangan',
            'Total Jam',
        ];
    }

    public function map($laporan): array
    {
        $data = [];
        $totalJam = 0;

        foreach ($laporan->detail_produksi as $detail) {
            $totalJam += $detail['persiapan'] + $detail['operation'] + $detail['reloading'] + $detail['gangguan'];
        }

        foreach ($laporan->detail_produksi as $index => $detail) {
            $data[] = [
                Carbon::parse($laporan->created_at)->format('d/m/Y'),
                $laporan->shift,
                $detail['op'],
                $detail['customers'],
                $detail['proses'],
                $detail['type_size'],
                $detail['ouput_per_order'],
                $laporan->hour_meter_akhir - $laporan->hour_meter_awal,
                $detail['persiapan'],
                $detail['operation'],
                $detail['reloading'],
                $detail['gangguan'],
                $detail['kendala'] ? implode(', ', $detail['kendala']) : '', // Pastikan kendala tidak null
                $detail['keterangan'],
                // implode(', ', $laporan->kendala ?? []),
                // strip_tags($laporan->keterangan),
                $index === 0 ? $totalJam : '',
            ];
        }

        return $data;
    }
}


// class LaporanExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithMultipleSheets
// {

//     protected $dates;
//     protected $startDate;
//     protected $endDate;

//     public function __construct($startDate = null, $endDate = null)
//     {
//         $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
//         $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
//     }
//     /**
//      * @return \Illuminate\Support\Collection
//      */
//     public function collection()
//     {
//         $query = Laporan::query();

//         // Filter data berdasarkan rentang tanggal
//         if ($this->startDate && $this->endDate) {
//             $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
//         } elseif ($this->startDate) {
//             $query->where('created_at', '>=', $this->startDate);
//         } elseif ($this->endDate) {
//             $query->where('created_at', '<=', $this->endDate);
//         }

//         return $query->orderBy('shift', 'asc')
//                      ->orderBy('created_at', 'asc')
//                      ->get();
//     }

//     public function headings(): array
//     {
//         return [
//             'Tanggal',
//             'Shift',
//             'OP',
//             'Customer',
//             'Proses',
//             'Type & Size',
//             'Output [m]',
//             'Hourmeter',
//             'P [Jam]',
//             'O [Jam]',
//             'R [Jam]',
//             'Kendala',
//             'Keterangan',
//             'Total Jam',
//         ];
//     }

//     /**
//      * Memetakan data ke dalam format yang sesuai
//      */
//     public function map($laporan): array
//     {
//         // Menyimpan array untuk data yang akan diekspor
//         $excelData = [];
//         $totalJam = 0;

//         foreach ($laporan->detail_produksi as $detail) {
//             $totalJam += $detail['persiapan'] + $detail['operation'] + $detail['reloading'];
//         }

//         // Mengiterasi semua elemen dalam detail_produksi
//         foreach ($laporan->detail_produksi as $index => $detail) {
//             $excelData[] = [
//                 Carbon::parse($laporan->created_at)->format('d/m/Y'), // Tanggal
//                 $laporan->shift, // Shift
//                 $detail['op'], // OP
//                 $detail['customers'], // Customer
//                 $detail['proses'], // Proses
//                 $detail['type_size'], // Type & Size
//                 $detail['ouput_per_order'], // Output [m]
//                 $laporan->hour_meter_akhir - $laporan->hour_meter_awal, // Hourmeter
//                 $detail['persiapan'], // P [Jam]
//                 $detail['operation'], // O [Jam]
//                 $detail['reloading'], // R [Jam]
//                 implode(',', $laporan->kendala ?? []),
//                 strip_tags($laporan->keterangan),
//                 $index === 0 ? $totalJam : '', // Total Jam, hanya untuk baris pertama
//             ];
//         }

//         return $excelData;
//     }

//     /**
//      * Nama Sheet Excel berdasarkan bulan dan tahun dari tanggal pertama
//      */
//     public function title(): string
//     {
//         return Carbon::parse($this->startDate)->format('d');
//     }
//     public function sheets(): array
//     {
//         $sheets = [];
//         $currentDate = $this->startDate;

//         // Generate semua tanggal antara start_date dan end_date
//         while ($currentDate <= $this->endDate) {
//             // Menambahkan laporan untuk setiap tanggal sebagai sheet terpisah
//             $sheets[] = new self($currentDate, $currentDate); // Buat instance LaporanExport baru per tanggal
//             $currentDate = $currentDate->addDay(); // Tambahkan satu hari
//         }

//         return $sheets;
//     }
// }
