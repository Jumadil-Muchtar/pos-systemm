<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use App\Filament\Resources\ProdukResource\RelationManagers\BarangsRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;


class ViewProduk extends ViewRecord
{
    protected static string $resource = ProdukResource::class;


    protected function getHeaderActions() : array {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make()
        ];
    }
    protected function getAllRelationManagers() : array {
        return [
            BarangsRelationManager::class
        ];
    }
    
}
