<?php

namespace App\Filament\Resources\CashBoxResource\Pages;

use App\Filament\Resources\CashBoxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashBox extends EditRecord
{
    protected static string $resource = CashBoxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
