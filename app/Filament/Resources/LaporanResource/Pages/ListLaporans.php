<?php

namespace App\Filament\Resources\LaporanResource\Pages;


use App\Filament\Resources\LaporanResource;
use App\Filament\Widgets\LatestLaporans;
use App\Models\Laporan;
use App\Models\Mesin;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support;
// use Filament\Pages\Concerns\ExposesTableToWidgets;


class ListLaporans extends ListRecords
{
    // use ExposesTableToWidgets;

    protected static string $resource = LaporanResource::class;

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         LatestLaporans::class,
    //     ];
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Export')
                ->color('primary')
                ->label('Export to Excel')
                ->form([
                    Forms\Components\Fieldset::make('label')
                        ->label('Pilih tanggal untuk export laporan')
                        ->schema([
                            Forms\Components\Select::make('mesin_id')
                                ->label('Pilih Mesin')
                                // ->options(Mesin::all()->pluck('nama_mesin', 'id'))
                                ->options(
                                    Laporan::query()
                                        ->with('mesins:id,nama_mesin')
                                        ->select('mesin_id')
                                        ->distinct()
                                        ->get()
                                        ->pluck('mesins.nama_mesin', 'mesin_id')
                                        ->toArray()
                                )
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('month')
                                ->label('Pilih Bulan')
                                ->type('month')
                                // ->hiddenLabel()
                                ->default(now()->format('Y-m')),
                            // Forms\Components\DatePicker::make('start_date')
                            //     ->label('Tanggal Mulai')
                            //     ->native(false)
                            //     ->closeOnDateSelection()
                            //     ->placeholder(now()->startOfMonth()->format('d/m/Y'))
                            //     ->defaultFocusedDate(now()->startOfMonth())
                            //     ->maxDate(now())
                            //     ->required(),
                            // Forms\Components\DatePicker::make('end_date')
                            //     ->label('Tanggal Akhir')
                            //     ->native(false)
                            //     ->closeOnDateSelection()
                            //     ->placeholder(now()->endOfMonth()->format('d/m/Y'))
                            //     ->defaultFocusedDate(now()->endOfMonth())
                            //     ->maxDate(now())
                            //     ->required(),
                            // Forms\Components\TextInput::make('start_date')
                            //     ->label('Start Date')
                            //     ->type('date')
                            //     ->required(),
                            // Forms\Components\TextInput::make('end_date')
                            //     ->label('End Date')
                            //     ->type('date')
                            //     ->required(),
                        ])
                        ->columns(2),
                ])
                ->modalWidth(Support\Enums\MaxWidth::TwoExtraLarge)
                ->action(function (array $data) {
                    $mesinId = $data['mesin_id'];
                    // $startDate = $data['start_date'];
                    // $endDate = $data['end_date'];
                    $month = $data['month'];

                    // dd($data);

                    // Notification::make()
                    //     ->title('Export Berhasil dibuat')
                    //     ->success()
                    //     ->seconds(5)
                    //     ->send();

                    // $this->dispatch('close-modal');

                    $url = route('export.laporan', [
                        'mesin_id' => $mesinId,
                        'month' => $month,
                        // 'start_date' => $startDate,
                        // 'end_date' => $endDate,
                    ]);

                    return redirect()->to($url);
                })
                ->modal()
                ->modalSubmitActionLabel('Export')
                ->closeModalByEscaping(false)
                ->modalCloseButton(false),
        ];
    }
}
