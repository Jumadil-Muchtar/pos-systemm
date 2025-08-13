<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PembelianResource\RelationManagers\BarangsRelationManager;

class ViewPembelian extends ViewRecord
{
    protected static string $resource = PembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    protected function getAllRelationManagers() : array {
        return [
            BarangsRelationManager::class
        ];
    }
}
