<?php

namespace App\Filament\Resources\HutangResource\Pages;

use App\Filament\Resources\HutangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\HutangResource\Widgets\StatistikHutangPelanggan;

class ListHutangs extends ListRecords
{
    protected static string $resource = HutangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs() : array{
        return [
            'transaksi' => Tab::make()
                            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('updated_at', 'desc')),
            'pelanggan' => Tab::make()
                            ->modifyQueryUsing(function (Builder $query) {
                                $subquery = DB::table('hutangs')
                                              ->select(DB::raw('MAX(id) as id'))
                                              ->groupBy('pelanggan_id');
                                return $query->whereIn('id', $subquery);
                            }),
        ];
    }
    protected function getHeaderWidgets() : array{
        return [
            StatistikHutangPelanggan::class,
        ];
    }
}