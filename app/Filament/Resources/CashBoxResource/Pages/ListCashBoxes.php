<?php

namespace App\Filament\Resources\CashBoxResource\Pages;

use App\Filament\Resources\CashBoxResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashBoxes extends ListRecords
{
    protected static string $resource = CashBoxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
