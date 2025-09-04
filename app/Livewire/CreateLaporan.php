<?php

namespace App\Livewire;

use App\Models\Laporan;
use App\Models\Mesin;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Support\RawJs;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
// use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use RuntimeException;

class CreateLaporan extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected static ?int $indexRepeater = 0;

    public function form(Form $form): Form
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
                            ->hint('Max 3 NIK')
                            ->relationship('karyawans', 'nik')
                            ->multiple()
                            ->searchable()
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
                            ->inputMode('decimal')
                            ->stripCharacters(['.', ',', ' ', "\u{00A0}"])
                            ->reactive()
                            ->maxValue(fn (Get $get) => (int) preg_replace('/\D+/', '', (string) $get('hour_meter_akhir'))),
                        Forms\Components\TextInput::make('hour_meter_akhir')
                            ->placeholder('input Hour Meter Akhir')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->inputMode('decimal')
                            ->stripCharacters(['.', ',', ' ', "\u{00A0}"])
                            ->reactive()
                            ->minValue(fn (Get $get) => (int) preg_replace('/\D+/', '', (string) $get('hour_meter_awal'))),
                    ])
                    ->columns(3),

                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\Fieldset::make('Label')
                            ->label('Production Details')
                            ->schema([
                                Forms\Components\Repeater::make('detail_produksi')
                                    ->hint('Anda bisa menambahkan hingga 5 detail produksi dalam satu laporan.')
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
                                                    ->live()
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
                                                        'Inner + Outer + Autocoiler' => 'Inner + Outer + Autocoiler',
                                                        'Insul + Autocoiler' => 'Insul + Autocoiler',
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
                                                    ->suffix(function (Get $get) {
                                                        return match ($get('proses')) {
                                                            'Drawing' => 'm/s',
                                                            default => 'm/min'
                                                        };
                                                    })
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


                // Forms\Components\Fieldset::make('Label')
                //     ->label('Kendala')
                //     ->schema([
                //         Forms\Components\ToggleButtons::make('feedback')
                //             ->label('ada kendala?')
                //             ->boolean()
                //             ->default(false)
                //             ->live()
                //             ->inline(),
                //     ]),

                // Forms\Components\Fieldset::make('Label')
                //     ->visible(fn(callable $get) => $get('feedback') == true)
                //     ->live()
                //     ->label('Issues & Additional Notes')
                //     ->schema([
                //         // Forms\Components\Radio::make('kendala')
                //         //     ->columns(5)
                //         //     ->options([
                //         //         'TOP' => 'TOP',
                //         //         'GO' => 'GO',
                //         //         'TBP' => 'TBP',
                //         //         'TBK' => 'TBK',
                //         //         'TPS' => 'TPS',
                //         //         'MR' => 'MR',
                //         //         'TAT' => 'TAT',
                //         //         'TAO' => 'TAO',
                //         //         'TB' => 'TB',
                //         //         'CO' => 'CO',
                //         //         'lain' => 'Lainnya',
                //         //     ]),
                //         Forms\Components\CheckboxList::make('kendala')
                //             ->label('Kendala')
                //             ->options([
                //                 'TOP' => 'TOP',
                //                 'GO' => 'GO',
                //                 'TBP' => 'TBP',
                //                 'TBK' => 'TBK',
                //                 'TPS' => 'TPS',
                //                 'MR' => 'MR',
                //                 'TAT' => 'TAT',
                //                 'TAO' => 'TAO',
                //                 'TB' => 'TB',
                //                 'CO' => 'CO',
                //                 'lain' => 'Lainnya',
                //             ])
                //             ->allowHtml()
                //             ->columns(5)
                //             ->gridDirection('row'),
                //         Forms\Components\RichEditor::make('keterangan')
                //             ->label('Keterangan')
                //             ->toolbarButtons([
                //                 'bold',
                //                 'h2',
                //                 'h3',
                //                 'orderedList',
                //                 'redo',
                //                 'undo',
                //             ]),
                //     ])
                //     ->columns(1),
            ])
            ->statePath('data')
            ->model(Laporan::class);
    }

    // public function submitForm(): \Filament\Actions\Action
    // {
    //     return \Filament\Actions\Action::make('submit')
    //         ->requiresConfirmation() // This is the key method for confirmation
    //         ->modalHeading('Confirm Submission')
    //         ->modalDescription('Are you sure you want to submit this form?')
    //         ->modalSubmitActionLabel('Submit')
    //         ->action(function () {
    //             // Your form submission logic goes here
    //             // e.g., $this->form->save();
    //         });
    // }

    public function createAction(): Action
    {
        return Action::make('create')
            ->requiresConfirmation()
            ->action(function () {
                $this->closeActionModal();
                // dd($this->form->getState());
                $post = Laporan::create($this->form->getState());
                $this->form->model($post)->saveRelationships();

                Notification::make()
                    ->title('Laporan Berhasil dibuat')
                    ->success()
                    ->seconds(5)
                    ->send();

                $this->form->fill();

                $this->js('window.scrollTo({ top: 0, behavior: "smooth" })');
            });
    }

    public function create()
    {
        // return \Filament\Actions\Action::make('save')
        //     ->label('Simpan')
        //     ->requiresConfirmation()
        //     ->action(function (Action $action, $form) {
        //         $data = $form->getState();
        //         dd($data); // Untuk test
        //         // Laporan::create($data);
        //     });
        dd($this->form->getState());
        // $post = Laporan::create($this->form->getState());

        // $this->form->model($post)->saveRelationships();

        // Notification::make()
        //     ->title('Laporan Berhasil dibuat')
        //     ->success()
        //     ->seconds(5)
        //     ->send();

        // $this->form->fill();
    }

    public function render()
    {
        return view('livewire.create-laporan');
    }

    public static function generateKodeLaporan($mesinId): string
    {
        $mesin = Mesin::find($mesinId);
        if (!$mesin) return 'Invalid Mesin';

        $plantCode = trim($mesin->nama_plant);
        $mesinCode = trim($mesin->nama_mesin);
        $yy        = now()->format('y');

        $width  = 5;                              // 5 digit
        $prefix = "{$plantCode}{$yy}{$mesinCode}-"; // contoh: A25AW-1-

        // Hanya ambil yang panjangnya pas: prefix + 5 char
        $likeFixed = $prefix . str_repeat('_', $width);

        // Ambil last berbasis angka suffix (MySQL)
        $last = Laporan::where('kode_laporan', 'like', $likeFixed)
            ->orderByRaw('CAST(RIGHT(kode_laporan, ?) AS UNSIGNED) DESC', [$width])
            ->first();

        $pattern = '/^' . preg_quote($prefix, '/') . '(\d{' . $width . '})$/';

        $next = 1;
        if ($last && preg_match($pattern, trim($last->kode_laporan), $m)) {
            $next = (int) $m[1] + 1;
        }

        $max = (10 ** $width) - 1; // 99999
        if ($next > $max) {
            // ganti sesuai kebijakanmu: ValidationException/notify Filament/return null
            throw new RuntimeException('Nomor urut sudah mencapai ' . $max . ' untuk tahun ini.');
        }

        return $prefix . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }

    // public static function generateKodeLaporan($mesinId)
    // {
    //     $mesin = Mesin::find($mesinId);
    //     if (!$mesin) return 'Invalid Mesin';

    //     $plantCode = $mesin->nama_plant;   // contoh: "A"
    //     $mesinCode = $mesin->nama_mesin;   // contoh: "EX-70/40"
    //     $yy        = now()->format('y');   // "25" untuk 2025

    //     // Prefix bawa plant + tahun 2 digit + nama mesin, dan tambahkan '-' agar angka tidak nempel ke "40"
    //     $prefix = "{$plantCode}{$yy}{$mesinCode}-"; // contoh: "A25EX-70/40-"

    //     // Cari kode terakhir untuk prefix tsb (otomatis reset tiap tahun karena YY beda)
    //     $last = Laporan::where('kode_laporan', 'like', $prefix . '%')
    //         ->orderBy('kode_laporan', 'desc')
    //         ->first();

    //     // Ambil tepat 3 digit setelah prefix
    //     $next = 1;
    //     if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{3})$/', $last->kode_laporan, $m)) {
    //         $next = (int)$m[1] + 1;
    //     }

    //     if ($next > 999) {
    //         // kalau mau, ganti jadi return atau log error sesuai kebutuhanmu
    //         throw new RuntimeException('Nomor urut sudah mencapai 999 untuk tahun ini.');
    //     }

    //     return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    // }



    // public static function generateKodeLaporan($mesinId)
    // {
    //     // Ambil mesin berdasarkan mesin_id
    //     $mesin = Mesin::find($mesinId);

    //     // Pastikan mesin ditemukan
    //     if (!$mesin) {
    //         return 'Invalid Mesin'; // Fallback jika mesin tidak ditemukan
    //     }

    //     // Ambil nama_plant dan nama_mesin dari mesin
    //     $plantCode = $mesin->nama_plant; // Nama plant dari mesin
    //     $mesinCode = $mesin->nama_mesin; // Nama mesin dari mesin

    //     // Ambil tanggal hari ini
    //     $today = now()->format('y');

    //     // Ambil nomor urut terakhir untuk hari ini berdasarkan mesin
    //     $lastReport = Laporan::whereDate('created_at', now()->toDateString())
    //         ->where('mesin_id', $mesinId)
    //         ->latest('id')
    //         ->first();

    //     // Tentukan nomor urut untuk laporan baru
    //     $urut = $lastReport ? str_pad((int) substr($lastReport->kode_laporan, -3) + 1, 3, '0', STR_PAD_LEFT) : '001';

    //     // Gabungkan menjadi kode laporan
    //     return "{$plantCode}{$today}{$mesinCode}{$urut}";
    // }
}
