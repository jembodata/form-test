<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laporan>
 */
class LaporanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    // {
    //     return [
    //         //
    //     ];
    // }
    {
        $prosesList = [
            'coloring',
            'tubing',
            'outer_sheath',
            'drop_cable',
            'inner_sheath',
            'stranding',
            'armour',
            'wrapping',
            'insulation',
            'screen',
            'taping',
            'armouring',
            'rewind',
            'repair',
            'inner_sheathing',
            'separation_sheathing',
            'outer_sheathing',
            'tin',
            'micatape',
            'twist',
            'cabling',
            'inner',
            'braiding',
            'outer',
            'rewind_marking',
            'drawing',
            'bunching',
            'insul',
            'coiling'
        ];

        // $detailProduksi = [
        //     [
        //         'op' => ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])] . rand(10000000, 99999999),
        //         'proses' => $prosesList[array_rand($prosesList)],
        //         'kendala' => ['TOP', 'MR'],
        //         'customers' => Str::random(10),
        //         'operation' => rand(1, 5),
        //         'persiapan' => rand(1, 5),
        //         'reloading' => rand(1, 5),
        //         'type_size' => Str::random(13),
        //         'keterangan' => '<p>' . Str::random(20) . '</p>',
        //         'ouput_per_order' => rand(500, 2000),
        //     ]
        // ];

        return [
            'kode_laporan' => ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])] . now()->format('Ymd') . '-' . rand(1000, 9999),
            'mesin_id' => rand(1, 224),
            'nik' => rand(1000, 9999),
            'shift' => rand(1, 3),
            'hour_meter_awal' => rand(1000, 5000),
            'hour_meter_akhir' => rand(5001, 10000),
            // 'detail_produksi' => json_encode($detailProduksi),
            'detail_produksi' => [
                [
                    'op' => ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])] . rand(10000000, 99999999),
                    'proses' => $prosesList[array_rand($prosesList)],
                    'kendala' => ['TOP', 'MR'],
                    'customers' => Str::random(10),
                    'operation' => rand(1, 5),
                    'persiapan' => rand(1, 5),
                    'reloading' => rand(1, 5),
                    'type_size' => Str::random(13),
                    'keterangan' => '<p>' . Str::random(20) . '</p>',
                    'ouput_per_order' => rand(500, 2000),
                ]
            ],
            'keterangan' => 'Keterangan contoh',
        ];
    }
}
