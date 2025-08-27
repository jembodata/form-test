<?php

use App\Models\Karyawan;
use App\Models\Laporan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nama');
            $table->string('departemen');
            $table->timestamps();
        });

        Schema::create('karyawan_laporan', function (Blueprint $table) {
            $table->id();
            // $table->foreignIdFor(Laporan::class)->index();
            // $table->foreignIdFor(Karyawan::class)->index();
            $table->foreignIdFor(Laporan::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Karyawan::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
        Schema::dropIfExists('karyawan_laporan');
    }
};
