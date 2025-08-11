<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pembelian;
class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    protected function mutateFormDataBeforeCreate(array $data) : array{
        $last_id = Pembelian::latest()->first()->id;
        if($last_id){
            $data['nomor_pembelian'] = 'PML-'.strval($last_id);
        }else{
            $data['nomor_pembelian'] = 'PML-1';
        }
        $data['total'] = 0;
        return $data;
    }
}
