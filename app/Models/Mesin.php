<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    //
    public function laporan()
    {
        return $this->hasMany(Laporan::class);
    }
}
