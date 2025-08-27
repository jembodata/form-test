<?php

namespace App\Filament\Resources;

use App\Filament\Exports\LaporanExporter;
use App\Filament\Resources\LaporanResource\Pages;
use App\Filament\Resources\LaporanResource\RelationManagers;
use App\Models\Laporan;
use App\Models\Mesin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Actions\Exports\Enums\ExportFormat;

class LaporanResource extends Resource
{
    protected static ?string $model = Laporan::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationLabel = 'Data Laporan';

    protected static ?int $indexRepeater = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('kode_laporan')
                            ->label('Kode Laporan')
                            ->placeholder('Auto Generate')
                            ->readOnly(), // Hanya menampilkan, tidak bisa diedit
                    ]),

                Forms\Components\Fieldset::make('Label')
                    ->label('Personal & Shift Information')
                    ->schema([
                        // Forms\Components\TextInput::make('nik')
                        //     ->placeholder('input nomor NIK')
                        //     ->label('NIK')
                        //     ->required()
                        //     ->numeric()
                        //     ->maxLength(4),
                        // Forms\Components\Select::make('pelapor_id')
                        //     ->label('Pelapor')
                        //     ->relationship('pelapor', 'nama')
                        //     ->searchable()
                        //     ->required(),
                        Forms\Components\Select::make('karyawans')
                            ->label('NIK')
                            ->relationship('karyawans', 'nik')
                            ->multiple()
                            ->searchable()
                            ->hint('Max 3 NIK')
                            ->required()
                            ->preload()
                            ->maxItems(3),
                        Forms\Components\Select::make('shift')
                            ->placeholder('Pilih Shift')
                            ->label('Shift')
                            ->native(false)
                            ->required()
                            ->options([
                                '1' => 'Shift 1',
                                '2' => 'Shift 2',
                                '3' => 'Shift 3',
                            ])
                            ->default(function () {
                                $now = now(); // pakai Carbon, ini default di Laravel
                                // Convert jam ke menit sejak 00:00 untuk gampang compare
                                $minutes = $now->hour * 60 + $now->minute;

                                $shift1_start = 6 * 60 + 45;   // 06:45 = 405 menit
                                $shift1_end   = 15 * 60 + 15;  // 15:15 = 915 menit

                                $shift2_start = 15 * 60 + 15;  // 15:15 = 915 menit
                                $shift2_end   = 22 * 60 + 45;  // 22:45 = 1365 menit

                                // Shift 3 dari 22:45 (hari ini) sampai 06:45 (besok)
                                // jadi 2 rentang: 22:45–24:00 DAN 00:00–06:45
                                if ($minutes >= $shift1_start && $minutes < $shift1_end) {
                                    return '1';
                                } elseif ($minutes >= $shift2_start && $minutes < $shift2_end) {
                                    return '2';
                                } else {
                                    return '3';
                                }
                            }),
                        Forms\Components\Select::make('plant_id')
                            ->placeholder('Pilih Plant')
                            ->required()
                            ->label('Plant')
                            ->native(false)
                            ->options(
                                Mesin::select('nama_plant')
                                    ->distinct()
                                    ->pluck('nama_plant', 'nama_plant')
                            )
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('mesin_id', null); // Reset mesin_id ketika plant_id berubah
                                $set('kode_laporan', null);
                            })
                            ->dehydrated(false),

                    ])
                    ->columns(3),

                Forms\Components\Fieldset::make('Label')
                    ->label('Equipment Information')
                    ->schema([
                        Forms\Components\Select::make('mesin_id')
                            ->placeholder('Pilih Mesin')
                            ->label('Nama Mesin')
                            ->native(false)
                            ->required()
                            ->live()
                            ->searchable()
                            ->disabled(fn(callable $get) => !$get('plant_id'))
                            ->options(function (callable $get) {
                                $plantId = $get('plant_id'); // Ambil plant_id yang dipilih
                                return Mesin::where('nama_plant', $plantId)  // Filter mesin berdasarkan nama_plant yang dipilih
                                    ->pluck('nama_mesin', 'id');  // Ambil nama_mesin dan id untuk options
                            })
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                // Setelah mesin_id dipilih, update kode_laporan
                                $mesinId = $get('mesin_id');
                                $kodeLaporan = self::generateKodeLaporan($mesinId);
                                $set('kode_laporan', $kodeLaporan); // Update kode_laporan
                            }),
                        Forms\Components\TextInput::make('hour_meter_awal')
                            ->placeholder('input Hour Meter Awal')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->inputMode('decimal'),
                        Forms\Components\TextInput::make('hour_meter_akhir')
                            ->placeholder('input Hour Meter Akhir')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->inputMode('decimal'),
                    ])
                    ->columns(3),

                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Fieldset::make('Label')
                            ->label('Production Details')
                            ->schema([
                                Forms\Components\Repeater::make('detail_produksi')
                                    ->schema([
                                        Forms\Components\Section::make([
                                            Forms\Components\TextInput::make('persiapan')
                                                ->label('Persiapan')
                                                ->placeholder('0')
                                                // ->default('0')
                                                ->numeric()
                                                ->suffix('Jam')
                                                ->live()
                                                ->minValue(0)
                                                ->maxValue(12)
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                    $livewire->validateOnly($component->getStatePath());
                                                }),
                                            Forms\Components\TextInput::make('operation')
                                                ->label('Operation')
                                                ->placeholder('0')
                                                // ->default('0')
                                                ->numeric()
                                                ->suffix('Jam')
                                                ->minValue(0)
                                                ->maxValue(12)
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                    $livewire->validateOnly($component->getStatePath());
                                                }),
                                            Forms\Components\TextInput::make('reloading')
                                                ->label('Reloading')
                                                ->placeholder('0')
                                                // ->default('0')
                                                ->numeric()
                                                ->suffix('Jam')
                                                ->minValue(0)
                                                ->maxValue(12)
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                    $livewire->validateOnly($component->getStatePath());
                                                }),
                                            Forms\Components\TextInput::make('gangguan')
                                                ->label('Gangguan')
                                                ->placeholder('0')
                                                // ->default('0')
                                                ->numeric()
                                                ->suffix('Jam')
                                                ->minValue(0)
                                                ->maxValue(12)
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                    $livewire->validateOnly($component->getStatePath());
                                                }),
                                        ])
                                            ->description('Mohon Teliti dan isi dengan benar')
                                            ->columns(4),
                                        Forms\Components\Section::make('')
                                            ->description('Mohon Teliti dan isi dengan benar')
                                            ->columns(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('customers')
                                                    ->placeholder('input nama pelanggan')
                                                    ->label('Customers')
                                                    ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                                    ->required(),
                                                Forms\Components\Select::make('proses')
                                                    ->placeholder('input nama proses')
                                                    ->label('Proses')
                                                    ->searchable()
                                                    ->required()
                                                    ->options([
                                                        'Coloring' => 'Coloring',
                                                        'Tubing' => 'Tubing',
                                                        'Outer Sheath' => 'Outer Sheath',
                                                        'Drop Cable' => 'Drop Cable',
                                                        'Inner Sheath' => 'Inner Sheath',
                                                        'Stranding' => 'Stranding',
                                                        'Armour' => 'Armour',
                                                        'Wrapping' => 'Wrapping',
                                                        'Insulation' => 'Insulation',
                                                        'Screen' => 'Screen',
                                                        'Taping' => 'Taping',
                                                        'Armouring' => 'Armouring',
                                                        'Rewind' => 'Rewind',
                                                        'Repair' => 'Repair',
                                                        'Inner Sheathing' => 'Inner Sheathing',
                                                        'Separation Sheathing' => 'Separation Sheathing',
                                                        'Outersheathing' => 'Outersheathing',
                                                        'Tin' => 'Tin',
                                                        'Micatape' => 'Micatape',
                                                        'Twist' => 'Twist',
                                                        'Cabling' => 'Cabling',
                                                        'Inner' => 'Inner',
                                                        'Braiding' => 'Braiding',
                                                        'Outer' => 'Outer',
                                                        'Rewind Marking' => 'Rewind Marking',
                                                        'Drawing' => 'Drawing',
                                                        'Bunching' => 'Bunching',
                                                        'Insul' => 'Insul',
                                                        'Coiling' => 'Coiling',
                                                    ])
                                                    ->native(false),
                                                Forms\Components\TextInput::make('op')
                                                    ->label('No OP')
                                                    ->placeholder('input nomor OP')
                                                    ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                                    ->maxLength(10)
                                                    ->required()
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                        $livewire->validateOnly($component->getStatePath());
                                                    }),
                                                Forms\Components\TextInput::make('type_size')
                                                    ->placeholder('input Type & Size')
                                                    ->label('Type & Size')
                                                    ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                                    ->required(),
                                                Forms\Components\TextInput::make('ouput_per_order')
                                                    ->label('Output')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->placeholder('input dalam angka')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->suffix('Meter')
                                                    ->required(),
                                                Forms\Components\TextInput::make('line_speed')
                                                    ->label('Line Speed')
                                                    ->placeholder('0')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                        $livewire->validateOnly($component->getStatePath());
                                                    }),
                                            ]),
                                        Forms\Components\Section::make('Kendala & Keterangan')
                                            ->description('Isi jika ada, Mohon Teliti dan isi dengan benar')
                                            ->collapsed()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\CheckboxList::make('kendala')
                                                    ->label('Kendala')
                                                    ->options([
                                                        'TOP' => 'TOP',
                                                        'GO' => 'GO',
                                                        'TBP' => 'TBP',
                                                        'TBK' => 'TBK',
                                                        'TPS' => 'TPS',
                                                        'MR' => 'MR',
                                                        'TAT' => 'TAT',
                                                        'TAO' => 'TAO',
                                                        'TB' => 'TB',
                                                        'CO' => 'CO',
                                                        'lain' => 'Lainnya',
                                                    ])
                                                    ->columns(2)
                                                    ->gridDirection('row')
                                                    ->rules([
                                                        function () {
                                                            return function (string $attribute, $value, $fail) {
                                                                if (count($value) > 3) {
                                                                    $fail("Kendala tidak boleh lebih dari 3 pilihan.");
                                                                }
                                                            };
                                                        },
                                                    ])
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function ($livewire, Forms\Components\CheckboxList $component) {
                                                        $livewire->validateOnly($component->getStatePath());
                                                    }),
                                                Forms\Components\Textarea::make('keterangan')
                                                    ->label('Keterangan')
                                                    ->rows(9)
                                                    ->cols(10)
                                                    ->disableGrammarly(),
                                            ]),
                                    ])
                                    ->collapsed()
                                    ->cloneable()
                                    ->deleteAction(
                                        fn(\Filament\Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                    )
                                    ->maxItems(5)
                                    ->columns(2)
                                    // ->itemLabel(function (array $state, $component): ?string {

                                    //     $key = array_search($state, $component->getState());
                                    //     $index = array_search($key, array_keys($component->getState()));

                                    //     return $index + 1;
                                    // }),
                                    ->itemLabel(fn() => __('Data') . ' ' . ++self::$indexRepeater)
                                    ->addActionLabel('Tambah Detail Produksi')
                            ])
                            ->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('dibuat pada')
                    ->date(),
                Tables\Columns\TextColumn::make('karyawans.nama')
                    ->label('Nama')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('feedback')
                //     ->label('Kendala')
                //     ->badge()
                //     ->getStateUsing(function ($record) {
                //         return $record->feedback ? 'Ada' : 'Tidak Ada';
                //     })
                //     ->color(fn(string $state): string => match ($state) {
                //         'Tidak Ada' => 'success',
                //         'Ada' => 'danger',
                //     })
                //     ->searchable(),
                Tables\Columns\TextColumn::make('kode_laporan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shift')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mesins.nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mesins.nama_plant')
                    ->label('Nama Plant')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('detail_produksi')
                //     ->state(function (Laporan $record) {
                //         $result = [];
                //         foreach ($record->detail_produksi as $detail_produksi) {
                //             $result[] = "No OP: {$detail_produksi['op']}, Proses: {$detail_produksi['proses']}, Type & Size: {$detail_produksi['type_size']}, Output per Order: {$detail_produksi['ouput_per_order']}";
                //         }
                //         return $result;
                //     })
                //     ->bulleted()
                //     ->searchable(),
                Tables\Columns\TextColumn::make('detail_produksi')
                    ->label('No OP')
                    ->state(function (Laporan $record) {
                        $result = [];
                        foreach ($record->detail_produksi as $detail_produksi) {
                            $result[] = "{$detail_produksi['op']}";
                        }
                        return $result;
                    })
                    ->searchable(),
                // ->bulleted(),

            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\ExportAction::make()
                //     ->exporter(LaporanExporter::class)
                //     ->formats([
                //         ExportFormat::Xlsx,
                //     ]),
                // Tables\Actions\Action::make('export_laporan')
                //     ->label('Export Laporan')
                //     ->closeModalByEscaping(false)
                //     ->form([
                //         Forms\Components\DatePicker::make('start_date')
                //             ->label('Tanggal Mulai')
                //             ->native(false)
                //             ->closeOnDateSelection()
                //             ->placeholder(now()->startOfMonth())
                //             ->defaultFocusedDate(now()->startOfMonth())
                //             ->maxDate(now())
                //             ->dehydrated(false)
                //             ->required(),
                //         Forms\Components\DatePicker::make('end_date')
                //             ->label('Tanggal Akhir')
                //             ->native(false)
                //             ->closeOnDateSelection()
                //             ->placeholder(now()->endOfMonth())
                //             ->defaultFocusedDate(now()->endOfMonth())
                //             ->maxDate(now())
                //             ->dehydrated(false)
                //             ->required(),
                //         // Forms\Components\Fieldset::make('label')
                //         //     ->label('Pilih Format Export')
                //         //     ->schema([])
                //         //     ->columns(2),
                //     ])
                //     // ->modalSubmitAction(false)
                //     ->action(function (array $data):void {
                //         // Retrieve start_date and end_date from the form data
                //         // $startDate = $data('start_date');
                //         // $endDate = $data('end_date');

                //         // // Redirect to the export route with start_date and end_date
                //         // return redirect()->route('export.laporan', [
                //         //     'start_date' => $startDate,
                //         //     'end_date' => $endDate,
                //         // ]);
                //         $data['start_date'];
                //         $data['end_date'];
                //     }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->form([
                            Forms\Components\Grid::make(4)
                                ->schema([
                                    Forms\Components\Fieldset::make('Label')
                                        ->label('Production Details')
                                        ->schema([
                                            Forms\Components\Repeater::make('detail_produksi')
                                                ->schema([
                                                    Forms\Components\Section::make([
                                                        Forms\Components\TextInput::make('persiapan')
                                                            ->label('Persiapan')
                                                            ->placeholder('0')
                                                            // ->default('0')
                                                            ->numeric()
                                                            ->suffix('Jam')
                                                            ->live()
                                                            ->minValue(0)
                                                            ->maxValue(12)
                                                            ->live(debounce: 500)
                                                            ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                                $livewire->validateOnly($component->getStatePath());
                                                            }),
                                                        Forms\Components\TextInput::make('operation')
                                                            ->label('Operation')
                                                            ->placeholder('0')
                                                            // ->default('0')
                                                            ->numeric()
                                                            ->suffix('Jam')
                                                            ->minValue(0)
                                                            ->maxValue(12)
                                                            ->live(debounce: 500)
                                                            ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                                $livewire->validateOnly($component->getStatePath());
                                                            }),
                                                        Forms\Components\TextInput::make('reloading')
                                                            ->label('Reloading')
                                                            ->placeholder('0')
                                                            // ->default('0')
                                                            ->numeric()
                                                            ->suffix('Jam')
                                                            ->minValue(0)
                                                            ->maxValue(12)
                                                            ->live(debounce: 500)
                                                            ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                                $livewire->validateOnly($component->getStatePath());
                                                            }),
                                                        Forms\Components\TextInput::make('gangguan')
                                                            ->label('Gangguan')
                                                            ->placeholder('0')
                                                            // ->default('0')
                                                            ->numeric()
                                                            ->suffix('Jam')
                                                            ->minValue(0)
                                                            ->maxValue(12)
                                                            ->live(debounce: 500)
                                                            ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                                $livewire->validateOnly($component->getStatePath());
                                                            }),
                                                    ])
                                                        ->description('Mohon Teliti dan isi dengan benar')
                                                        ->columns(4),
                                                    Forms\Components\Section::make('')
                                                        ->description('Mohon Teliti dan isi dengan benar')
                                                        ->columns(3)
                                                        ->schema([
                                                            Forms\Components\TextInput::make('customers')
                                                                ->placeholder('input nama pelanggan')
                                                                ->label('Customers')
                                                                ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                                                ->required(),
                                                            Forms\Components\Select::make('proses')
                                                                ->placeholder('input nama proses')
                                                                ->label('Proses')
                                                                ->searchable()
                                                                ->required()
                                                                ->options([
                                                                    'Coloring' => 'Coloring',
                                                                    'Tubing' => 'Tubing',
                                                                    'Outer Sheath' => 'Outer Sheath',
                                                                    'Drop Cable' => 'Drop Cable',
                                                                    'Inner Sheath' => 'Inner Sheath',
                                                                    'Stranding' => 'Stranding',
                                                                    'Armour' => 'Armour',
                                                                    'Wrapping' => 'Wrapping',
                                                                    'Insulation' => 'Insulation',
                                                                    'Screen' => 'Screen',
                                                                    'Taping' => 'Taping',
                                                                    'Armouring' => 'Armouring',
                                                                    'Rewind' => 'Rewind',
                                                                    'Repair' => 'Repair',
                                                                    'Inner Sheathing' => 'Inner Sheathing',
                                                                    'Separation Sheathing' => 'Separation Sheathing',
                                                                    'Outersheathing' => 'Outersheathing',
                                                                    'Tin' => 'Tin',
                                                                    'Micatape' => 'Micatape',
                                                                    'Twist' => 'Twist',
                                                                    'Cabling' => 'Cabling',
                                                                    'Inner' => 'Inner',
                                                                    'Braiding' => 'Braiding',
                                                                    'Outer' => 'Outer',
                                                                    'Rewind Marking' => 'Rewind Marking',
                                                                    'Drawing' => 'Drawing',
                                                                    'Bunching' => 'Bunching',
                                                                    'Insul' => 'Insul',
                                                                    'Coiling' => 'Coiling',
                                                                ])
                                                                ->native(false),
                                                            Forms\Components\TextInput::make('op')
                                                                ->label('No OP')
                                                                ->placeholder('input nomor OP')
                                                                ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                                                ->maxLength(10)
                                                                ->required()
                                                                ->live(debounce: 500)
                                                                ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                                    $livewire->validateOnly($component->getStatePath());
                                                                }),
                                                            Forms\Components\TextInput::make('type_size')
                                                                ->placeholder('input Type & Size')
                                                                ->label('Type & Size')
                                                                ->extraInputAttributes(['onInput' => 'this.value = this.value.toUpperCase()'])
                                                                ->required(),
                                                            Forms\Components\TextInput::make('ouput_per_order')
                                                                ->label('Output')
                                                                ->mask(RawJs::make('$money($input)'))
                                                                ->stripCharacters(',')
                                                                ->placeholder('input dalam angka')
                                                                ->numeric()
                                                                ->inputMode('decimal')
                                                                ->suffix('Meter')
                                                                ->required(),
                                                            Forms\Components\TextInput::make('line_speed')
                                                                ->label('Line Speed')
                                                                ->placeholder('0')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->live(debounce: 500)
                                                                ->afterStateUpdated(function ($livewire, Forms\Components\TextInput $component) {
                                                                    $livewire->validateOnly($component->getStatePath());
                                                                }),
                                                        ]),
                                                    Forms\Components\Section::make('Kendala & Keterangan')
                                                        ->description('Isi jika ada, Mohon Teliti dan isi dengan benar')
                                                        ->collapsed()
                                                        ->columns(2)
                                                        ->schema([
                                                            Forms\Components\CheckboxList::make('kendala')
                                                                ->label('Kendala')
                                                                ->options([
                                                                    'TOP' => 'TOP',
                                                                    'GO' => 'GO',
                                                                    'TBP' => 'TBP',
                                                                    'TBK' => 'TBK',
                                                                    'TPS' => 'TPS',
                                                                    'MR' => 'MR',
                                                                    'TAT' => 'TAT',
                                                                    'TAO' => 'TAO',
                                                                    'TB' => 'TB',
                                                                    'CO' => 'CO',
                                                                    'lain' => 'Lainnya',
                                                                ])
                                                                ->columns(2)
                                                                ->gridDirection('row')
                                                                ->rules([
                                                                    function () {
                                                                        return function (string $attribute, $value, $fail) {
                                                                            if (count($value) > 3) {
                                                                                $fail("Kendala tidak boleh lebih dari 3 pilihan.");
                                                                            }
                                                                        };
                                                                    },
                                                                ])
                                                                ->live(debounce: 500)
                                                                ->afterStateUpdated(function ($livewire, Forms\Components\CheckboxList $component) {
                                                                    $livewire->validateOnly($component->getStatePath());
                                                                }),
                                                            Forms\Components\Textarea::make('keterangan')
                                                                ->label('Keterangan')
                                                                ->rows(9)
                                                                ->cols(10)
                                                                ->disableGrammarly(),
                                                        ]),
                                                ])
                                                ->collapsed()
                                                ->cloneable()
                                                ->deleteAction(
                                                    fn(\Filament\Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                                )
                                                ->maxItems(5)
                                                ->columns(2)
                                                // ->itemLabel(function (array $state, $component): ?string {

                                                //     $key = array_search($state, $component->getState());
                                                //     $index = array_search($key, array_keys($component->getState()));

                                                //     return $index + 1;
                                                // }),
                                                ->itemLabel(fn() => __('Data') . ' ' . ++self::$indexRepeater)
                                                ->addActionLabel('Tambah Detail Produksi')
                                        ])
                                        ->columns(1),
                                ]),
                        ])
                ]),


                // Tables\Actions\EditAction::make()
                //     ->mutateRecordDataUsing(function (array $data) {
                //         // Ambil mesin_id dari data yang ada
                //         $mesin = Mesin::find($data['mesin_id']);

                //         if ($mesin) {
                //             // Tentukan plant_id berdasarkan mesin yang terhubung
                //             $data['plant_id'] = $mesin->nama_plant;
                //             $data['mesin_id'] = $mesin->nama_mesin;
                //         }

                //         // Mengembalikan data yang sudah dimodifikasi
                //         return $data;
                //     })
                //     ->form([
                //         Forms\Components\Select::make('plant_id')
                //             ->required()
                //             ->label('Plant')
                //             ->options(
                //                 Mesin::select('nama_plant')
                //                     ->distinct()
                //                     ->pluck('nama_plant', 'nama_plant')
                //             )
                //             ->live()
                //             ->dehydrated(false),
                //         Forms\Components\Select::make('mesin_id')
                //             ->label('Nama Mesin')
                //             ->required()
                //             ->live()
                //             ->searchable()
                //             ->options(function (callable $get) {
                //                 $plantId = $get('plant_id'); // Ambil plant_id yang dipilih
                //                 return Mesin::where('nama_plant', $plantId)  // Filter mesin berdasarkan nama_plant yang dipilih
                //                     ->pluck('nama_mesin', 'id');  // Ambil nama_mesin dan id untuk options
                //             })
                //     ]),
                // Tables\Actions\Action::make('Adresy')
                //     ->modalFooterActions(fn() => [])
                //     ->hiddenLabel()
                //     ->tooltip('Adres dostawy')
                //     ->icon('heroicon-o-map-pin')
                //     ->infolist(function (?Model $record, Infolist $infolist) {
                //     $addresses = [0 => ['default' => 1, 'adr_Nazwa' => 'test']];
                //     return $infolist->state(['addresses' => $addresses])
                //     ->schema([RepeatableEntry::make('addresses')
                //     ->schema([IconEntry::make('default')
                //     ->boolean()
                //     ->label('Domyślny'), TextEntry::make('adr_Nazwa'),])]);
                // })->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporans::route('/'),
            // 'create' => Pages\CreateLaporan::route('/create'),
            // 'edit' => Pages\EditLaporan::route('/{record}/edit'),
        ];
    }

    public static function generateKodeLaporan($mesinId)
    {
        // Ambil mesin berdasarkan mesin_id
        $mesin = Mesin::find($mesinId);

        // Pastikan mesin ditemukan
        if (!$mesin) {
            return 'Invalid Mesin'; // Fallback jika mesin tidak ditemukan
        }

        // Ambil nama_plant dan nama_mesin dari mesin
        $plantCode = $mesin->nama_plant; // Nama plant dari mesin
        $mesinCode = $mesin->nama_mesin; // Nama mesin dari mesin

        // Ambil tanggal hari ini
        $today = now()->format('Ymd');

        // Ambil nomor urut terakhir untuk hari ini berdasarkan mesin
        $lastReport = Laporan::whereDate('created_at', now()->toDateString())
            ->where('mesin_id', $mesinId)
            ->latest('id')
            ->first();

        // Tentukan nomor urut untuk laporan baru
        $urut = $lastReport ? str_pad((int) substr($lastReport->kode_laporan, -3) + 1, 3, '0', STR_PAD_LEFT) : '001';

        // Gabungkan menjadi kode laporan
        return "{$plantCode}-{$today}-{$mesinCode}-{$urut}";
    }
}
