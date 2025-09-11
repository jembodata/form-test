<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\LaporanExport;
use App\Models\Mesin;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function exportToExcel(Request $request)
    {

        // $startDate = $request->input('start_date');
        // $endDate = $request->input('end_date');

        // // Pastikan format tanggal sesuai yang dibutuhkan (Y-m-d)
        // $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
        // $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

        // $startDate = $request->input('start_date');
        // $endDate = $request->input('end_date');
        // $Idmesin = $request->input('mesin_id');
        // $month = $request->input('month');

        // $namaMesin = Mesin::find($Idmesin)->nama_mesin ?? 'nama_mesin';
        // $fileName = $namaMesin . '_' . str_replace('/', '-', $month) . '.xlsx';

        // return Excel::download(new LaporanExport($Idmesin, $month), $fileName);

        // return Excel::download(new LaporanExport($Idmesin, $month), 'laporan.xlsx');

        // return Excel::download(new LaporanExport, 'laporan.xlsx');
        $Idmesin = $request->input('mesin_id');
        $month = $request->input('month');

        $namaMesin = Mesin::find($Idmesin)->nama_mesin ?? 'nama_mesin';

        $safeNamaMesin = preg_replace('/[\/\\\\:*?"<>|]/', '-', $namaMesin);
        $safeMonth = preg_replace('/[\/\\\\:*?"<>|]/', '-', $month);

        $fileName = $safeNamaMesin . '_' . $safeMonth . '.xlsx';

        return Excel::download(new LaporanExport($Idmesin, $month), $fileName);
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
