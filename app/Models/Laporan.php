<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Laporan extends Model
{
    //
    use HasFactory;

    public function mesins()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    // public function pelapor(): BelongsTo
    // {
    //     return $this->belongsTo(Karyawan::class, 'pelapor_id');
    // }

    public function karyawans(): BelongsToMany
    {
        return $this->belongsToMany(Karyawan::class, 'karyawan_laporan');
    }

    protected $casts = [
        'detail_produksi' => 'array',
        'kendala' => 'array',
    ];

    protected $fillable = [
        'kode_laporan',
        'mesin_id',
        // 'nik',
        'pelapor_id',
        'shift',
        'hour_meter_awal',
        'hour_meter_akhir',
        'detail_produksi',
        'keterangan'
    ];
}
