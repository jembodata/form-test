<?php

namespace App\Filament\Exports;

use App\Models\Laporan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use PhpParser\Node\Stmt\Label;

class LaporanExporter extends Exporter
{
    protected static ?string $model = Laporan::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('kode_laporan'),
            ExportColumn::make('mesins.nama_mesin')
                ->label('Nama Mesin'),
            ExportColumn::make('mesins.nama_plant')
                ->label('Nama Plant'),
            ExportColumn::make('nik'),
            ExportColumn::make('shift'),
            ExportColumn::make('hour_meter_awal'),
            ExportColumn::make('hour_meter_akhir'),
            ExportColumn::make('detail_produksi')
                ->listAsJson(),
            ExportColumn::make('feedback')
                ->label('Kendala'),
            ExportColumn::make('kendala'),
            ExportColumn::make('keterangan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your laporan export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
