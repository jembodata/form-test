<?php

namespace App\Filament\Resources\MesinResource\Pages;

use App\Filament\Resources\MesinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMesins extends ListRecords
{
    protected static string $resource = MesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
