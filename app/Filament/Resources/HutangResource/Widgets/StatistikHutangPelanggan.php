<?php

namespace App\Filament\Resources\HutangResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Pelanggan;

class StatistikHutangPelanggan extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pelanggan', Pelanggan::all()->count()),
            Stat::make('Pelanggan Berutang', Pelanggan::where('utang', '>', 0)->count()),
            Stat::make('Total Utang', Pelanggan::all()->sum('utang')),
        ];
    }
}
