<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\SaldoToko;
use Illuminate\Database\Eloquent\Model;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    protected function mutateFormDataBeforeCreate(array $data) : array{
        $pembelian = Pembelian::latest()->first();

        if($pembelian){
            $data['nomor_pembelian'] = 'PML-'.strval($pembelian->id);
        }else{
            $data['nomor_pembelian'] = 'PML-1';
        }
        $data['total'] = 0;
        return $data;
    }
    protected function handleRecordCreation(array $data) : Model{
        $total = 0;

        foreach($data['barangs'] as $key=>$dataa){
            $total += ($dataa['harga_beli'] * $dataa['jumlah']); 
        }
        $pembelian = Pembelian::create([
            'nomor_pembelian' => $data['nomor_pembelian'],
            'total' => $total,
            'tanggal_pembelian' => $data['tanggal_pembelian'],
            'pemasok_id' => $data['pemasok_id']
        ]);


        if($pembelian){
            $nama_pemasok = '-';
            $pemasok = $pembelian->pemasok;
            if($pemasok && $pemasok->nama != null){
                $nama_pemasok = $pemasok->nama;
            }
            $pengeluaran = SaldoToko::create([
                'catatan' => 'Melakukan pembelian barang di toko '.$nama_pemasok,
                'kategori' => 'Pengeluaran',
                'jumlah' => $total,
                'tanggal' => now()
            ]);
            foreach($data['barangs'] as $key=>$dataa){
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
        return $pembelian;
    }
}
