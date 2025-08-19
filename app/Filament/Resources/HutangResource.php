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
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\SaldoToko;
use Filament\Tables\Actions\Action;


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
                ->limit(30)
                ->searchable(),
            Tables\Columns\TextColumn::make('bayar')
                ->label('Keterangan')
                ->formatStateUsing(fn (bool $state): string =>$state ? 'Bayar Hutang': 'Tambah Hutang'),
            Tables\Columns\TextColumn::make('jumlah')
                ->label('Utang')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('pelanggan.utang')
                ->label('Total Utang')
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
                    ->limit(30),
            Tables\Columns\TextColumn::make('bayar')
                    ->label('Keterangan')
                    ->badge()
                    ->color(fn (bool $state) : string => $state ? 'success' : 'warning')
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
                Action::make('bayar_hutang')
                    ->modal()
                    ->form([
                        Forms\Components\DateTimePicker::make('tanggal')->required()->seconds(false)->default(now()),
                        Forms\Components\TextInput::make('jumlah_dibayarkan')->required(),
                        Forms\Components\TextInput::make('catatan'),
                        Forms\Components\Checkbox::make('pakai_saldo_pelanggan'),
                    ])->action(function (Hutang $record, array $data){
                        $pelanggan = Pelanggan::find($record->pelanggan_id);
                        $penjualan_terakhir = null;
                        if ($pelanggan){
                            $sisa_saldo = 0;
                            $sisa_uang = $data['jumlah_dibayarkan'];
                            if($data['pakai_saldo_pelanggan'] == true && $pelanggan->saldo > 0){
                                $sisa_uang = $sisa_uang + $pelanggan->saldo;
                                $pelanggan->saldo = 0;
                                $pelanggan->save();
                            }
                            $penjualan = Penjualan::where('status', 'Belum Lunas')
                                            ->where('jumlah_utang', '>', 0)
                                            ->where('pelanggan_id', $pelanggan->id)
                                            ->orderBy('tanggal_penjualan')
                                            ->get();
                            foreach($penjualan as $key => $value){
                                if($sisa_uang <= 0){
                                    break;
                                }
                                if($value != null && $value->jumlah_utang <= $sisa_uang){
                                    $pelanggan->utang = $pelanggan->utang - $value->jumlah_utang;
                                    $pelanggan->save();
                                    $bayar_hutang = Hutang::create([
                                        'catatan' => $data['catatan'],
                                        'jumlah' => $value->jumlah_utang,
                                        'pelanggan_id' => $pelanggan->id,
                                        'penjualan_id' => $value->id,
                                        'tanggal' => $data['tanggal'],
                                        'bayar' => true
                                    ]);
                                    $nomor_penjualan = '-';
                                    if($value->nomor_penjualan != null){
                                        $nomor_penjualan = $value->nomor_penjualan;
                                    }
                                    $pemasukan = $value->jumlah_utang;
                                    if($data['pakai_saldo_pelanggan'] == true){
                                        $pemasukan = 0;
                                    }else{
                                        $penambahan_saldo_toko = SaldoToko::create([
                                            'catatan' => 'Pemasukan dari pembayaran utang pada penjualan nomor '.$nomor_penjualan.' sebanyak Rp. '. number_format( $pemasukan),
                                            'kategori' => 'Pemasukan',
                                            'jumlah' =>  $pemasukan,
                                            'tanggal' => now(),               
                                        ]);
                                    }
                                    
                                    $sisa_uang = $sisa_uang - $value->jumlah_utang;
                                    $value->update([
                                        'tanggal_pelunasan' => $data['tanggal'],
                                        'jumlah_utang' => 0,
                                        'jumlah_dibayar' => $value->jumlah_dibayar + $value->jumlah_utang,
                                        'status' => 'Sudah Lunas'
                                    ]);
                                }else if($value != null){
                                    $sisa_utang = $value->jumlah_utang - $sisa_uang;
                                    $telah_dibayar = $value->jumlah - $sisa_utang; 
                                    $pelanggan->utang = $sisa_utang;
                                    $pelanggan->save();
                                    $bayar_hutang = Hutang::create([
                                        'catatan' => $data['catatan'],
                                        'jumlah' => $sisa_uang,
                                        'pelanggan_id' => $pelanggan->id,
                                        'penjualan_id' => $value->id,
                                        'tanggal' => $data['tanggal'],
                                        'bayar' => true
                                    ]);    
                                    $nomor_penjualan = '-';
                                    if($value->nomor_penjualan != null){
                                        $nomor_penjualan = $value->nomor_penjualan;
                                    }
                                    $pemasukan = $sisa_uang;
                                    if($data['pakai_saldo_pelanggan'] == true){
                                        $pemasukan = 0;
                                    } else {
                                        sleep(1);
                                        $saldo_sebelumnya = SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah');
                                        $penambahan_saldo_toko = SaldoToko::create([
                                            'catatan' => 'Pemasukan dari pembayaran utang pada penjualan nomor '.$nomor_penjualan.' sebanyak Rp. '. number_format( $pemasukan),
                                            'kategori' => 'Pemasukan',
                                            'jumlah' =>  $pemasukan,
                                            'tanggal' => now(),               
                                            'saldo_sebelumnya' => $saldo_sebelumnya,               
                                        ]);
                                    }                               
                                    $value->update([
                                        'jumlah_utang' => $sisa_utang,
                                        'jumlah_dibayar' => $telah_dibayar,
                                        'status' => 'Belum Lunas'
                                    ]);
                                    $sisa_uang = 0;
                                } 
                                $penjualan_terakhir = $value;                               
                            }
                            if($sisa_uang > 0){
                                $penjualan_id = null;
                                $nomor_penjualan = '-';
                                if($penjualan_terakhir != null){
                                    $penjualan_id = $penjualan_terakhir->id;
                                    $nomor_penjualan = $penjualan_terakhir->nomor_penjualan;
                                }
                                $penambahan_saldo = SaldoPelanggan::create([
                                    'catatan' => 'Membayar utang pada transaksi nomor '.$nomor_penjualan.' namun masih tersisa uang sebanyak Rp. '.number_format($sisa_uang),
                                    'jumlah' => $sisa_uang,
                                    'pelanggan_id' => $pelanggan->id,
                                    'penjualan_id' => $penjualan_id,
                                    'tanggal' => now()
                                ]);
                                $penambahan_saldo_toko = SaldoToko::create([
                                    'catatan' => 'Isi saldo pelanggan '.$pelanggan->nama.' dari sisa uang pembayaran utang pada penjualan nomor '.$nomor_penjualan.' sejumlah Rp. '. number_format( $sisa_uang),
                                    'kategori' => 'Pemasukan',
                                    'jumlah' =>  $sisa_uang,
                                    'tanggal' => now(),               
                                ]);
                                $pelanggan->saldo = $pelanggan->saldo + $sisa_uang;
                                $pelanggan->save();

                            }
                        }
                    }),
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
