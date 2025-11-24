<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Laporan extends Model
{
    //
    use HasFactory;

    public function mesins()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    public function karyawans(): BelongsToMany
    {
        return $this->belongsToMany(Karyawan::class, 'karyawan_laporan');
    }

    protected $casts = [
        'detail_produksi' => 'array',
        'kendala' => 'array',
    ];

    protected $fillable = [
        // 'kode_laporan',
        'mesin_id',
        'pelapor_id',
        'shift',
        'hour_meter_awal',
        'hour_meter_akhir',
        'detail_produksi',
        'keterangan'
    ];

    protected static function booted(): void
    {
        static::creating(function (Laporan $laporan) {
            // biar aman kalau suatu saat kamu set manual untuk seeding
            if (! empty($laporan->kode_laporan)) {
                return;
            }

            $laporan->kode_laporan = self::nextKodeForMesin($laporan->mesin_id);
        });
    }

    public static function nextKodeForMesin(int $mesinId): string
    {
        return DB::transaction(function () use ($mesinId) {
            $mesin = Mesin::findOrFail($mesinId);

            $plantCode = trim($mesin->nama_plant);
            $mesinCode = trim($mesin->nama_mesin);
            $yy        = now()->format('y');
            $width     = 5;
            $prefix    = "{$plantCode}{$yy}{$mesinCode}-"; // ex: A25AW-1-

            // lock baris yang relevan supaya anti race-condition
            $last = DB::table('laporans')
                ->where('kode_laporan', 'like', $prefix . str_repeat('_', $width))
                ->lockForUpdate()
                ->orderByRaw('CAST(RIGHT(kode_laporan, ?) AS UNSIGNED) DESC', [$width])
                ->first();

            $next = $last ? ((int) substr($last->kode_laporan, -$width) + 1) : 1;
            $max  = (10 ** $width) - 1;
            if ($next > $max) {
                throw new RuntimeException("Nomor urut sudah mencapai {$max} untuk tahun ini.");
            }

            return $prefix . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
        });
    }
}
