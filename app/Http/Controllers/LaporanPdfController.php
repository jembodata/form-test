<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPdfController extends Controller
{
    //
    public function stream(Laporan $laporan)
    {
        // Ambil relasi yang dibutuhkan
        $laporan->loadMissing([
            'mesins:id,nama_plant,nama_mesin',
            'karyawans:id,nama,nik',
        ]);

        // Render Blade -> PDF bytes
        $pdf = Pdf::loadView('pdf.laporan', [
            'laporan' => $laporan,
        ])->setPaper('a4', 'portrait');

        // Stream inline di tab (Content-Disposition: inline)
        return $pdf->stream('laporan-'.$laporan->id.'.pdf');
    }
}
