<?php

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
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->string('kode_laporan')->unique();
            // $table->string('nik');
            // $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->string('shift');
            $table->string('hour_meter_awal');
            $table->string('hour_meter_akhir');
            $table->json('detail_produksi');
            $table->boolean('feedback')->default(false);
            $table->json('kendala')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
