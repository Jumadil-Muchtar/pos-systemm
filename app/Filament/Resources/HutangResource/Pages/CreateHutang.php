<?php

namespace App\Filament\Resources\HutangResource\Pages;

use App\Filament\Resources\HutangResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pelanggan;
use App\Models\Hutang;
use App\Models\SaldoPelanggan;

class CreateHutang extends CreateRecord
{
    protected static string $resource = HutangResource::class;

    protected function afterCreate() : void {
        if(isset($this->data['pelanggan_id'])){
            $pelanggan_id = $this->data['pelanggan_id'];
            $pelanggan = Pelanggan::find($pelanggan_id);
            if($pelanggan){
                $hutang = Hutang::where('pelanggan_id', $pelanggan_id)->where('bayar', false)->sum('jumlah');
                $bayar_hutang = Hutang::where('pelanggan_id', $pelanggan_id)->where('bayar', true)->sum('jumlah');
                if ($bayar_hutang > $hutang){
                    $pelanggan->utang = 0;
                    SaldoPelanggan::create([
                        'catatan' => 'Sisa dari pembayaran hutang.',
                        'jumlah' => $bayar_hutang - $hutang,
                        'pelanggan_id' => $pelanggan_id,
                        'tanggal' => now()
                    ]);
                    $saldo = SaldoPelanggan::where('pelanggan_id', $pelanggan_id)->sum('jumlah');
                    $pelanggan->saldo = $saldo; 
                }else if($bayar_hutang == $hutang){
                    $pelanggan->utang = 0;
                }else{
                    $pelanggan->utang = $hutang - $bayar_hutang;
                }
                $pelanggan->save();
            }
        }
    }
}
