<?php

namespace App\Filament\Widgets;

use App\Models\Laporan;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLaporans extends BaseWidget
{

    // protected static ?int $indexRepeater = 0;


    public function table(Table $table): Table
    {
        return $table
            ->heading('Data laporan')
            ->description('5 Laporan terbaru')
            ->query(
                // ...
                Laporan::orderBy('created_at', 'desc')
            )
            ->columns([
                // ...

                TextColumn::make('created_at')->label('dibuat pada')->dateTime('d M Y H:i'),
                TextColumn::make('karyawans.nama')->label('Nama')->listWithLineBreaks(),
                TextColumn::make('karyawans.nik')->label('NIK')->listWithLineBreaks(),
                TextColumn::make('mesins.nama_plant')->label('Plant'),
                TextColumn::make('mesins.nama_mesin')->label('Mesin'),
                TextColumn::make('shift')->label('Shift'),
                TextColumn::make('detail_produksi')
                    ->label('No OP')
                    ->state(function (Laporan $record) {
                        $result = [];
                        foreach ($record->detail_produksi as $detail_produksi) {
                            $result[] = "{$detail_produksi['op']}";
                        }
                        return $result;
                    })
                    ->listWithLineBreaks(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Laporan from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Laporan until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                ], layout: FiltersLayout::Modal)
            ->actions([
                // Tables\Actions\ViewAction::make()
                //     ->label('Lihat Laporan')
                //     ->record(fn($record) => $record) // Pass the current record to the action
                //     ->form([
                //         Forms\Components\Grid::make(4)
                //             ->schema([
                //                 Forms\Components\Fieldset::make('Label')
                //                     ->label('Production Details')
                //                     ->schema([
                //                         Forms\Components\Repeater::make('detail_produksi')
                //                             ->itemLabel(fn() => __('Data') . ' ' . ++self::$indexRepeater)
                //                             ->schema([
                //                                 Forms\Components\TextInput::make('persiapan'),
                //                                 Forms\Components\TextInput::make('operation'),
                //                                 Forms\Components\TextInput::make('reloading'),
                //                                 Forms\Components\TextInput::make('gangguan')
                //                             ])
                //                     ])
                //             ])
                //     ])
                // Tables\Actions\ViewAction::make()
                // ->form([
                //     Forms\Components\Grid::make(4)
                //         ->schema([
                //             Forms\Components\Fieldset::make('Label')
                //                 ->label('Production Details')
                //                 ->schema([
                //                     Forms\Components\Repeater::make('detail_produksi')
                //                         ->schema([

                //                         ])
                //                 ])
                //         ])
                // ]),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5]);
    }
}
