<?php

namespace App\Filament\Resources\SaldoTokoResource\Pages;

use App\Filament\Resources\SaldoTokoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\SaldoTokoResource\Widgets\SaldoTokoWidget;
use App\Models\SaldoToko;
use Illuminate\Database\Eloquent\Model;

class ManageSaldoTokos extends ManageRecords
{
    protected static string $resource = SaldoTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->using(function (array $data, string $model): Model {
                $data['saldo_sebelumnya'] = SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah');
                return $model::create($data);
            })

,
        ];
    }
    protected function getHeaderWidgets() : array{
        return [
            SaldoTokoWidget::class,
        ];
    }
}
