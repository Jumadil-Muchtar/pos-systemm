<?php

namespace App\Filament\Resources\HutangResource\Pages;

use App\Filament\Resources\HutangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHutang extends EditRecord
{
    protected static string $resource = HutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
