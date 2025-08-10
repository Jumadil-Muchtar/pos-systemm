<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HutangResource\Pages;
use App\Filament\Resources\HutangResource\RelationManagers;
use App\Models\Hutang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Forms\Get;
use App\Models\SaldoPelanggan;
use App\Filament\Resources\HutangResource\Widgets\StatistikHutangPelanggan;


class HutangResource extends Resource
{
    protected static ?string $model = Hutang::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('catatan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('pelanggan_id')
                    ->relationship('pelanggan', 'nama')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_hp')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat')
                            ->required()
                            ->columnSpanFull(),
                    ])->required(),   
                Forms\Components\DateTimePicker::make('tanggal')
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),            
                Forms\Components\Checkbox::make('bayar')
                    ->label('Bayar hutang'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $livewire = $table->getLivewire();
        $activeTab = $livewire->activeTab;
        $transaksiColumns = [
            Tables\Columns\TextColumn::make('pelanggan.nama')
                ->label('Nama Pelanggan')
                ->limit(50)
                ->searchable(),
            Tables\Columns\TextColumn::make('bayar')
                ->label('Keterangan')
                ->formatStateUsing(fn (bool $state): string =>$state ? 'Bayar Hutang': 'Tambah Hutang'),
            Tables\Columns\TextColumn::make('jumlah')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('tanggal')
                ->searchable()
                ->sortable(),
        ];
        $pelangganColumns = [
            Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Nama Pelanggan')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
            Tables\Columns\TextColumn::make('bayar')
                    ->label('Keterangan')
                    ->formatStateUsing(fn (bool $state): string =>$state ? 'Bayar Hutang': 'Tambah Hutang'),
            Tables\Columns\TextColumn::make('pelanggan.utang')
                ->label('Jumlah Utang')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('tanggal')
                ->sortable()
                ->searchable(),
        ];
        $columns = match ($activeTab) {
            'pelanggan' => $pelangganColumns,
            default => $transaksiColumns,
        };

        return $table
            ->columns($columns)
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('bayar')
                    ->label('Keterangan')
                    ->options([
                        true => 'Bayar Hutang',
                        false => 'Tambah Hutang'
                    ]),
                Tables\Filters\SelectFilter::make('pelanggan')
                    ->relationship('pelanggan', 'nama')
                    ->searchable()
                    ->preload(),
            ])->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHutangs::route('/'),
            'create' => Pages\CreateHutang::route('/create'),
            'view' => Pages\ViewHutang::route('/{record}'),
            'edit' => Pages\EditHutang::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function infolist(Infolist $infolist) : Infolist{
        return $infolist->schema([
            Section::make('Informasi Hutang')->schema([
                Infolists\Components\TextEntry::make('bayar')
                    ->label('Keterangan')
                    ->formatStateUsing(fn (bool $state): string =>$state ? 'Bayar Hutang': 'Tambah Hutang'),
                Infolists\Components\TextEntry::make('pelanggan.nama')
                    ->label('Nama Pelanggan'),
                Infolists\Components\TextEntry::make('jumlah'),
                Infolists\Components\TextEntry::make('catatan'),
                Infolists\Components\TextEntry::make('tanggal')
                    ->label('Tanggal'),
                Infolists\Components\TextEntry::make('total_hutang')
                    ->label('Total Hutang Pelanggan (Sekarang)')
                    ->state(function (Hutang $record) : string{
                        // $hutang = Hutang::where('pelanggan_id', $record->pelanggan_id)->where('bayar', false)->sum('jumlah');
                        // $bayar_hutang = Hutang::where('pelanggan_id', $record->pelanggan_id)->where('bayar', true)->sum('jumlah');
                        // if ($bayar_hutang >= $hutang){
                        //     return 'Rp. 0';
                        // }else{
                        //     return 'Rp. '.strval($hutang - $bayar_hutang);
                        // }
                        return 'Rp. '.strval($record->pelanggan->utang);
                    }),
                Infolists\Components\TextEntry::make('total_saldo')
                    ->label('Saldo Pelanggan (Sekarang)')
                    ->state(function (Hutang $record) : string{
                        return 'Rp. '.strval($record->pelanggan->saldo);
                    }),
            ]),
        ]);
    }
    public static function getWidgets() : array {
        return [
            StatistikHutangPelanggan::class,
        ];
    }
}
