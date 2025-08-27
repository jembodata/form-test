<?php

namespace Database\Seeders;

use App\Models\Laporan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Laporan::factory(100)->create();
        // $prosesList = [
        //     'coloring', 'tubing', 'outer_sheath', 'drop_cable', 'inner_sheath',
        //     'stranding', 'armour', 'wrapping', 'insulation', 'screen', 'taping',
        //     'armouring', 'rewind', 'repair', 'inner_sheathing', 'separation_sheathing',
        //     'outer_sheathing', 'tin', 'micatape', 'twist', 'cabling', 'inner',
        //     'braiding', 'outer', 'rewind_marking', 'drawing', 'bunching', 'insul', 'coiling'
        // ];

        // Laporan::factory(5)->create()->each(function ($laporan) use ($prosesList) {
        //     $detailProduksi = [
        //         [
        //             'op' => ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])] . rand(10000000, 99999999),
        //             'proses' => $prosesList[array_rand($prosesList)],
        //             'kendala' => ['TOP', 'MR'],
        //             'customers' => Str::random(10),
        //             'operation' => rand(1, 5),
        //             'persiapan' => rand(1, 5),
        //             'reloading' => rand(1, 5),
        //             'type_size' => Str::random(13),
        //             'keterangan' => Str::random(20),
        //             'ouput_per_order' => rand(500, 2000)
        //         ]
        //     ];

        //     $laporan->update([
        //         'kode_laporan' => 'LAP-' . now()->format('Ymd') . '-' . rand(1000, 9999),
        //         'mesin_id' => rand(1, 5),
        //         'nik' => 'NIK-' . rand(1000, 9999),
        //         'shift' => rand(1, 3),
        //         'hour_meter_awal' => rand(1000, 5000),
        //         'hour_meter_akhir' => rand(5001, 10000),
        //         'detail_produksi' => json_encode($detailProduksi),
        //         'keterangan' => 'Keterangan contoh',
        //     ]);
        // });
    }
}
