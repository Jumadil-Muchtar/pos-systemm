<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Filament\Resources\PenjualanResource\RelationManagers;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Barang;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Hutang;
use App\Models\SaldoToko;
use App\Models\SaldoPelanggan;
use Closure;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Log;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\PenjualanResource\RelationManagers\BarangTerjualsRelationManager;
class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form) : Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pelanggan_id')
                    ->relationship('pelanggan', 'nama')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\DateTimePicker::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->default(now())
                    ->seconds(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('tanggal_pelunasan')
                    ->label('Tanggal Pelunasan')
                    ->seconds(false),
                Forms\Components\Toggle::make('utang')
                    ->required(),
                Forms\Components\Textarea::make('catatan'),
                Forms\Components\TextInput::make('jumlah_utang')
                    ->numeric(),
                Forms\Components\Repeater::make('barang_terjuals')
                    ->schema([
                        Forms\Components\Select::make('barang_id')
                            ->label('Nama Produk')
                            ->options(
                                Barang::where('stok', '>', 0)->get()
                                    ->mapWithKeys(function ($barang){
                                        return [
                                            $barang->id => "{$barang->produk->nama} (Ex: {$barang->tanggal_kedaluwarsa}) - Tersisa : {$barang->stok}",
                                        ];
                                    })->sortDesc()->toArray()
                            )->searchable()
                            ->preload()
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state){
                                $barang = Barang::find($state);
                                if($barang){
                                    $set('harga_satuan', $barang->harga_jual);
                                    $set('harga', $barang->harga_jual);
                                    $set('stok', $barang->stok);
                                    $jumlah = intval($get('jumlah'));
                                    $set('sub_total', $barang->harga_jual * $jumlah);
                                }
                            })->required(),
                        Forms\Components\TextInput::make('harga')
                            ->numeric()
                            ->readOnly()
                            ->default(0),
                        Forms\Components\TextInput::make('jumlah')
                            ->numeric()
                            ->live(true)
                            ->minValue(1)
                            ->default(1)
                            ->afterStateUpdated(function (callable $set, callable $get){
                                $hargaSatuan = intval($get('harga_satuan'));
                                $jumlah = intval($get('jumlah'));
                                $set('sub_total', $hargaSatuan * $jumlah);
                            })->required(),
                        Forms\Components\Hidden::make('harga_satuan'),
                        Forms\Components\Hidden::make('stok'),
                        Forms\Components\TextInput::make('sub_total')
                            ->numeric()
                            ->readOnly()
                            ->default(0),
                    ])->collapsible()
                    ->defaultItems(1)
                    ->columns(3)
                    ->addActionLabel('Tambah Produk')
                    ->columnSpanFull(),                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_penjualan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Nama Pelanggan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('jumlah_utang')
                    ->label('Sisa Utang')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('bayar_hutang')
                    ->modal()
                    ->form([
                        Forms\Components\DateTimePicker::make('tanggal')->required()->seconds(false)->default(now()),
                        Forms\Components\TextInput::make('jumlah_dibayarkan')->required(),
                        Forms\Components\TextInput::make('catatan'),                        
                        Forms\Components\Checkbox::make('pakai_saldo_pelanggan'),
                    ])->action(function (Penjualan $record, array $data){
                            $pelanggan = Pelanggan::find($record->pelanggan_id);
                            if($pelanggan){
                                $total_uang = $data['jumlah_dibayarkan'];
                                if($data['pakai_saldo_pelanggan'] == true){
                                    $total_uang += $pelanggan->saldo;
                                    $pelanggan->saldo = 0;                                
                                }
                                // uang pengguna tidak cukup
                                if ($record->jumlah_utang > $total_uang){
                                    $pelanggan->utang = $pelanggan->utang - $total_uang;
                                    $bayar_hutang = Hutang::create([
                                        'catatan' => $data['catatan'],
                                        'jumlah' => $total_uang,
                                        'pelanggan_id' => $pelanggan->id,
                                        'penjualan_id' => $record->id,
                                        'tanggal' => $data['tanggal'],
                                        'bayar' => true
                                    ]);
                                    $pemasukan = $total_uang;
                                    if($data['pakai_saldo_pelanggan'] == true){
                                        $pemasukan = 0;
                                    }else{
                                        sleep(1);
                                        $saldo_sebelumnya = SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah');
                                        $penambahan_saldo_toko = SaldoToko::create([
                                            'catatan' => 'Pemasukan dari pembayaran utang pada penjualan nomor '.$record->nomor_penjualan.' sebanyak Rp. '. number_format( $pemasukan),
                                            'kategori' => 'Pemasukan',
                                            'jumlah' =>  $pemasukan,
                                            'tanggal' => now(),               
                                            'saldo_sebelumnya' => $saldo_sebelumnya,               
                                        ]);
                                    }
                                    $record->update([
                                        'tanggal_pelunasan' => $data['tanggal'],
                                        'jumlah_utang' => $record->jumlah_utang - $total_uang,
                                        'jumlah_dibayar' => $record->jumlah_dibayar + $total_uang ,
                                        'status' => 'Belum Lunas'
                                    ]);
                                // Uang pengguna cukup atau lebih
                                }else{
                                    $pelanggan->saldo = $pelanggan->saldo + ($total_uang - $record->jumlah_utang);
                                    $bayar_hutang = Hutang::create([
                                        'catatan' => $data['catatan'],
                                        'jumlah' => $record->jumlah_utang,
                                        'pelanggan_id' => $pelanggan->id,
                                        'penjualan_id' => $record->id,
                                        'tanggal' => $data['tanggal'],
                                        'bayar' => true
                                    ]);
                                    $pelanggan->utang = $pelanggan->utang - $record->jumlah_utang ;
                                    
                                    $pemasukan = $record->jumlah_utang;
                                    if($data['pakai_saldo_pelanggan'] == true){
                                        $pemasukan = 0;
                                    }else{
                                        sleep(1);
                                        $saldo_sebelumnya = SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah');
                                        $penambahan_saldo_toko = SaldoToko::create([
                                            'catatan' => 'Pemasukan dari pembayaran utang pada penjualan nomor '.$record->nomor_penjualan.' sebanyak Rp. '. number_format( $pemasukan),
                                            'kategori' => 'Pemasukan',
                                            'jumlah' =>  $pemasukan,
                                            'tanggal' => now(),               
                                            'saldo_sebelumnya' => $saldo_sebelumnya,
                                        ]);
                                    }
                                    $sisa_uang = $total_uang - $record->jumlah_utang;
                                    if($sisa_uang > 0){
                                        $penambahan_saldo = SaldoPelanggan::create([
                                            'catatan' => 'Membayar utang pada transaksi nomor '.$record->nomor_penjualan.' namun masih tersisa uang sebanyak Rp. '.number_format($sisa_uang),
                                            'jumlah' => $sisa_uang,
                                            'pelanggan_id' => $pelanggan->id,
                                            'penjualan_id' => $record->id,
                                            'tanggal' => now()
                                        ]);
                                        $saldo_sebelumnya = SaldoToko::where('kategori', 'Pemasukan')->sum('jumlah') + SaldoToko::where('kategori', 'Saldo Pelanggan')->sum('jumlah') - SaldoToko::where('kategori', 'Pengeluaran')->sum('jumlah');
                                        $penambahan_saldo_toko = SaldoToko::create([
                                            'catatan' => 'Pemasukan dari sisa uang  sebanyak Rp. '. number_format( $sisa_uang).' pada pembayaran utang penjualan nomor '.$record->nomor_penjualan,
                                            'kategori' => 'Pemasukan',
                                            'jumlah' =>  $sisa_uang,
                                            'tanggal' => now(),               
                                            'saldo_sebelumnya' => $saldo_sebelumnya,
                                        ]);
                                    }
                                    $record->update([
                                        'tanggal_pelunasan' => $data['tanggal'],
                                        'jumlah_utang' => 0,
                                        'jumlah_dibayar' => $record->jumlah,
                                        'status' => 'Sudah Lunas'
                                    ]);

                                    sleep(1);

                                }
                                $pelanggan->save();
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
            BarangTerjualsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'view' => Pages\ViewPenjualan::route('/{record}'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist{
        return $infolist->schema([
            TextEntry::make('pelanggan.nama')
                ->label('Nama Pelanggan'),
            TextEntry::make('tanggal_penjualan')
                ->label('Tanggal Penjualan')
                ->dateTime(timezone: 'Asia/Makassar'),
            TextEntry::make('tanggal_pelunasan')
                ->label('Tanggal Pelunasan')
                ->default('-'),
            TextEntry::make('utang')
                ->label('Berhutang')
                ->color('primary')
                ->state(function (Penjualan $record){
                    if($record->utang){
                        return 'Ya';
                    }else{
                        return 'Tidak';
                    }
                }),
            TextEntry::make('jumlah_utang')
                ->label('Jumlah Hutang')
                ->default('Rp. 0')
                ->state(function (Penjualan $record){
                    $jumlah_utang = $record->jumlah - $record->jumlah_dibayar;
                    return 'Rp. '.number_format($jumlah_utang);
                }),
            TextEntry::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Dibayar' => 'gray',
                    'Belum Lunas' => 'warning',
                    'Sudah Lunas' => 'success',
                    'Dilupakan' => 'danger',
                })

        ]);
    }
}
