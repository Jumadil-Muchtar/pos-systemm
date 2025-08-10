<?php

namespace App\Filament\Resources\SaldoTokoResource\Pages;

use App\Filament\Resources\SaldoTokoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSaldoTokos extends ManageRecords
{
    protected static string $resource = SaldoTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
