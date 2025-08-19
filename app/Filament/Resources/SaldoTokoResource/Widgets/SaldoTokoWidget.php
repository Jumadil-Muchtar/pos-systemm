<?php

namespace App\Filament\Resources\SaldoTokoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SaldoToko;
use App\Models\Barang;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;

class SaldoTokoWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Saldo', 
                'Rp. '.number_format(
                    (
                        SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') 
                        + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') 
                        - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah')
                    )
                )
            ),
            Stat::make('Total Modal', 
                'Rp. '.number_format(
                    (
                        SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') 
                        - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah')
                    ) + Barang::where('stok', '>', 0)
                            ->sum(DB::raw('stok * harga_beli'))
                    + Pelanggan::all()->sum('utang')
                )
            ), 
        ];
    }
}
