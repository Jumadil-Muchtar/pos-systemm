<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SaldoToko;
use App\Models\BarangTerjual;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardWidget extends BaseWidget
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
            Stat::make('Total Hutang', 
                'Rp. '. number_format(
                    Pelanggan::all()->sum('utang')
                )
            ),
            Stat::make('Omzet Hari Ini', 
                'Rp. '.number_format(
                    BarangTerjual::where('created_at', '>=', Carbon::today()->startOfDay()->toDateTimeString())->sum('total_harga')
                )
            ),
            Stat::make('Keuntungan Hari Ini', 
                'Rp. '. number_format(
                    BarangTerjual::where('created_at', '>=', Carbon::today()->startOfDay()->toDateTimeString())->sum('keuntungan')
                )
            ),
            
        ];
    }
}
