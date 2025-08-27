<?php

use App\Http\Controllers\LaporanController;
use App\Livewire\CreateLaporan;
use App\Livewire\CreatePatient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('posts/create', CreatePatient::class);

Route::get('laporan/create', CreateLaporan::class);

// Route::get('/export-laporan', [LaporanController::class, 'exportToExcel']);
Route::get('/export-laporan', [LaporanController::class, 'exportToExcel'])->name('export.laporan');