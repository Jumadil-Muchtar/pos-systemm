<?php

namespace App\Filament\Resources\PelangganResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use Carbon\Carbon;

class StatistikPelanggan extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pelanggan', Pelanggan::all()->count()),
            Stat::make('Pelanggan Mingguan', 
                    Penjualan::where(
                        'created_at', '>=', Carbon::now()->startOfWeek()->toDateString()
                    )->groupBy('pelanggan_id')->count()),
            Stat::make('Pelanggan Bulanan', 
                    Penjualan::where(
                        'created_at', '>=', Carbon::now()->startOfMonth()->toDateString()
                    )->groupBy('pelanggan_id')->count()),
        ];
    }
    // protected function getStats(): array
    // {
    //     return [
    //         Stat::make('Total Pelanggan', Pelanggan::all()->count()),
    //         Stat::make('Pelanggan Mingguan', Pelanggan::where('created_at', '>=', Carbon::now()->startOfMonth())->count()),
    //         Stat::make('Pelanggan Bulanan', 
    //             Pelanggan::join('penjualans', 'penjualans.pelanggan_id', 'pelanggans.id')
    //                 ->join('barangs', 'barangs.penjualan_id', 'penjualans.id')
    //                 ->sum('harga_jual')),
    //     ];
    // }
}
