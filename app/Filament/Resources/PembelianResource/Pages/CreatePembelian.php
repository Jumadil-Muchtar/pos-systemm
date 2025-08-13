<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pembelian;
use App\Models\Barang;
use Illuminate\Database\Eloquent\Model;

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
    protected function handleRecordCreation(array $data) : Model{
        // dd($data);
        $total = 0;

        foreach($data['barangs'] as $key=>$dataa){
            // dd($dataa);
            $total += ($dataa['harga_beli'] * $dataa['jumlah']); 
        }
        $pembelian = Pembelian::create([
            'nomor_pembelian' => $data['nomor_pembelian'],
            'total' => $total,
            'tanggal_pembelian' => $data['tanggal_pembelian'],
            'pemasok_id' => $data['pemasok_id']
        ]);

        if($pembelian){
            foreach($data['barangs'] as $key=>$dataa){
                // dd($dataa);
                $barang_baru = Barang::create([
                    'tanggal_kedaluwarsa' => $dataa['tanggal_kedaluwarsa'],
                    'produk_id' => $dataa['produk_id'],
                    'pembelian_id' => $pembelian->id,
                    'gambar' => $dataa['gambar'],
                    'harga_beli' => $dataa['harga_beli'],
                    'harga_jual' => $dataa['harga_jual'],
                    'stok' => $dataa['stok'],
                    'jumlah' => $dataa['jumlah'],
                    'dipajang' => $dataa['dipajang'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]); 
            }
        }
        // $data['total'] = $total;
        // $pembelian = parent::handleRecordCreation($data);

        // $pembeliann = Pembelian::find($pembelian->id);
        // $pembeliann->load('barangs');
        // foreach ($pembeliann->barangs as $barang) {
        //     $total += ($barang->harga_jual * $barang->jumlah);
        // }
        // $pembeliann->total = $total;
        // $pembeliann->save();
        // dd($pembelian->barangs);
        return $pembelian;
    }
}
