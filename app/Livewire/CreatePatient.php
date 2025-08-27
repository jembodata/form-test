<?php

namespace App\Livewire;

use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class CreatePatient extends Component implements HasForms
{
    use InteractsWithForms;

    // public Patient $post;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Label')
                    ->label('Personal & Shift Information')
                    ->schema([
                        // DatePicker::make('date_of_birth')
                        //     ->native(false)
                        //     ->maxDate(now()),
                        // TextInput::make('name')
                        //     ->required(),
                        // Select::make('type')
                        //     ->native(false)
                        //     ->required()
                        //     ->options([
                        //         'cat' => 'Cat',
                        //         'dog' => 'Dog',
                        //         'rabbit' => 'Rabbit',
                        //     ]),
                        // Select::make('owner_id')
                        //     ->native(false)
                        //     ->relationship('owner', 'name')
                        //     ->searchable()
                        //     ->preload()
                        //     ->createOptionForm([
                        //         TextInput::make('name')
                        //             ->required()
                        //             ->maxLength(255),
                        //         TextInput::make('email')
                        //             ->label('Email address')
                        //             ->email()
                        //             ->required()
                        //             ->maxLength(255),
                        //         TextInput::make('phone')
                        //             ->label('Phone number')
                        //             ->tel()
                        //             ->required(),
                        //     ])
                        //     ->required(),
                        TextInput::make('name')
                            ->label('NIK')
                            ->numeric()
                            ->maxLength(4)
                            ->required(),
                        Select::make('Shift')
                            ->label('Shift')
                            ->native(false)
                            ->required()
                            ->options([
                                '1' => 'Shift 1',
                                '2' => 'Shift 2',
                                '3' => 'Shift 3',
                            ]),
                        Select::make('Plant')
                            ->label('Plant')
                            ->native(false)
                            ->required()
                            ->options([
                                'A' => 'PLANT A',
                                'B' => 'PLANT B',
                                'C' => 'PLANT C',
                                'D' => 'PLANT D',
                                'E' => 'PLANT E',
                                'AUTO' => 'AUTOWIRE',
                            ]),

                    ])
                    ->columns(3),

                Fieldset::make('Label')
                    ->label('Equipment Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Mesin ')
                            ->required(),
                        TextInput::make('name')
                            ->label('Hour Meter Awal ')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('name')
                            ->label('Hour Meter Akhir ')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                    ])
                    ->columns(3),

                Fieldset::make('Label')
                    ->label('Production Details')
                    ->schema([
                        Repeater::make('Details')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Proses')
                                    ->required(),
                                TextInput::make('name')
                                    ->label('No OP')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('name')
                                    ->label('Type & Size')
                                    ->required(),
                                TextInput::make('name')
                                    ->label('Output per Order')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->required(),
                            ])
                            ->cloneable()
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->maxItems(5)
                            ->columns(4),
                    ])
                    ->columns(1),

                Fieldset::make('Label')
                    ->label('Issues & Additional Notes')
                    ->schema([
                        CheckboxList::make('kendala')
                            ->label('Kendala')
                            ->options([
                                'top' => 'TOP',
                                'go' => 'GO',
                                'tbp' => 'TBP',
                                'tbk' => 'TBK',
                                'tps' => 'TPS',
                                'mr' => 'MR',
                                'tat' => 'TAT',
                                'tao' => 'TAO',
                                'tb' => 'TB',
                                'lain' => 'Lainnya',
                            ])
                            ->allowHtml()
                            ->columns(5)
                            ->gridDirection('row'),
                        Textarea::make('description')
                            ->autosize()
                    ])
                    ->columns(1),

            ])
            ->statePath('data')
            ->model(Patient::class);
    }

    public function create(): void
    {
        // dd($this->form->getState());
        $post = Patient::create($this->form->getState());

        $this->form->model($post)->saveRelationships();

        Notification::make()
            ->title('Patient created successfully')
            ->success()
            ->seconds(5)
            ->send();

        $this->form->fill();
    }

    public function render(): View
    {
        return view('livewire.create-patient');
    }
}
