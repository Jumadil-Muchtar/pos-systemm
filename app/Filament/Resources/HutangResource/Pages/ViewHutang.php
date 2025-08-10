<?php

namespace App\Filament\Resources\HutangResource\Pages;

use App\Filament\Resources\HutangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHutang extends ViewRecord
{
    protected static string $resource = HutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
