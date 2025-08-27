<?php

namespace App\Filament\Resources\MesinResource\Pages;

use App\Filament\Resources\MesinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMesin extends EditRecord
{
    protected static string $resource = MesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
