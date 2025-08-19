<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\BarangTerjual;
use App\Models\Pelanggan;
use App\Models\Hutang;
use App\Models\SaldoToko;
use App\Models\SaldoPelanggan;
use Illuminate\Database\Eloquent\Model;
class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array {
        $penjualan = Penjualan::latest()->first();
        if($penjualan){
            $data['nomor_penjualan'] = 'PNJ-'.strval($penjualan->id);
        }else{
            $data['nomor_penjualan'] = 'PNJ-1';
        }
        return $data;

    }
    protected function handleRecordCreation(array $data) : Model{
        $total = 0;
        $jumlah_utang = 0;
        $catatan = '-';

        foreach ($data['barang_terjuals'] as $key => $dataa) {
            $barang = Barang::find($dataa['barang_id']);
            if ($barang){
                $sub_total = $dataa['jumlah'] * $dataa['harga'];
                $total += $sub_total;
            }                
        }
        $status = '';
        if($data['utang'] == true){
            $status = 'Belum Lunas';
            $jumlah_utang = $total;
            if($data['jumlah_utang'] != null){
                $jumlah_utang = $data['jumlah_utang'];
            }
            $catatan = '';
            if($data['catatan'] == null){
                $catatan = 'Transaksi nomor '.$data['nomor_penjualan'].' dengan jumlah utang Rp. '.number_format($jumlah_utang);
            }else{
                $catatan = $data['catatan'];
            }

            $pelanggan = Pelanggan::find($data['pelanggan_id']);
            if($pelanggan){
                $pelanggan->utang = $pelanggan->utang + $jumlah_utang;
                $pelanggan->save();
            }
        }else{
            $status = 'Dibayar';
        }
        $penjualan = Penjualan::create([
            'nomor_penjualan' => $data['nomor_penjualan'],
            'tanggal_pelunasan' => $data['tanggal_pelunasan'],
            'utang' => $data['utang'],
            'jumlah_utang' => $jumlah_utang,
            'pelanggan_id' => $data['pelanggan_id'],
            'tanggal_penjualan' => $data['tanggal_penjualan'],
            'jumlah' => $total,
            'status' => $status,
            'jumlah_dibayar' => $total - $jumlah_utang,
        ]);
        $pelanggan = Pelanggan::find($data['pelanggan_id']);
        if($pelanggan && $data['utang'] == true){
            $hutang_baru = Hutang::create([
                'catatan' => $catatan,
                'jumlah' => $jumlah_utang,
                'bayar' => false,
                'pelanggan_id' => $pelanggan->id,
                'penjualan_id' => $penjualan->id,
                'tanggal' => now()
            ]);
        }else if ($pelanggan){
            $saldo_sebelumnya = SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah');
            $nomor_penjualan = $data['nomor_penjualan'];
            $penambahan_saldo_toko = SaldoToko::create([
                'catatan' => 'Transaksi nomor '.$data['nomor_penjualan'].' dibayar dengan jumlah Rp. '.number_format($total - $jumlah_utang),
                'kategori' => 'Pemasukan',
                'jumlah' =>  $total - $jumlah_utang,
                'tanggal' => now(), 
                'saldo_sebelumnya' => $saldo_sebelumnya              
            ]);
        }
        foreach ($data['barang_terjuals'] as $key => $dataa) {
            $barang = Barang::find($dataa['barang_id']);
            if ($barang){
                $sub_total = $dataa['jumlah'] * $dataa['harga'];
                $keuntungan = $dataa['jumlah'] * ($barang->harga_jual - $barang->harga_beli);
                $barang->stok = $barang->stok - $dataa['jumlah'];
                if($barang->dipajang <= $dataa['jumlah']){
                    $barang->dipajang = 0;
                }else{
                    $barang->dipajang = $barang->dipajang - $dataa['jumlah'];
                }
                $barang->save();
                $barang_terjual = BarangTerjual::create([
                    'jumlah' => $dataa['jumlah'],
                    'barang_id' => $barang->id,
                    'total_harga' => $sub_total,
                    'keuntungan' => $keuntungan,
                    'penjualan_id' => $penjualan->id,
                ]);
            }                
        }
        return $penjualan;
    }
}
