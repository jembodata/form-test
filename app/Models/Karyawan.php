<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    //
    protected $fillable = ['nik', 'nama', 'departemen'];

    // public function laporanDipelopori(): HasMany
    // {
    //     return $this->hasMany(Laporan::class, 'pelapor_id');
    // }

    public function laporans(): BelongsToMany
    {
        return $this->belongsToMany(Laporan::class, 'karyawan_laporan');
    }
}
