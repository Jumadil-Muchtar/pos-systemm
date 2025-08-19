<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\RelationManagers;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Produk;
use App\Models\KategoriProduk;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;


class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('tanggal_pembelian')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('pemasok_id')
                    ->relationship('pemasok', 'nama')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nama')
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\Repeater::make('barangs')
                    ->schema([
                        Forms\Components\Select::make('produk_id')
                            ->label('Nama Produk')
                            ->options(fn () => Produk::pluck('nama', 'id'))
                            ->createOptionForm([
                                Forms\Components\Textarea::make('nama')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('barcode')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('kode')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('kategori_id')
                                    ->options(fn () => KategoriProduk::pluck('nama', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama')
                                            ->maxLength(255)
                                            ->required(),
                                    ])->createOptionUsing(function (array $data) {
                                        return KategoriProduk::create($data)->id;
                                    })->required(),
                                Forms\Components\FileUpload::make('gambar')
                                    ->image(),
                            ])->createOptionUsing(function (array $data) {
                                return Produk::create($data)->id;
                            })->searchable(),
                        Forms\Components\DatePicker::make('tanggal_kedaluwarsa')
                            ->required(),
                        Forms\Components\TextInput::make('harga_beli')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('harga_jual')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Dibeli')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('stok')
                            ->label('Stok')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('dipajang')
                            ->label('Jumlah Dipajang')
                            ->numeric()
                            ->required(),
                        Forms\Components\FileUpload::make('gambar')
                            ->image(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pemasok.nama')
                    ->label('Penjual'),
                Tables\Columns\TextColumn::make('total'),
                Tables\Columns\TextColumn::make('tanggal_pembelian')
                    ->sortable()
                    ->label('Tanggal'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            RelationManagers\PemasokRelationManager::class,
            RelationManagers\ProdukRelationManager::class,
            RelationManagers\BarangsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'view' => Pages\ViewPembelian::route('/{record}'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
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
            Infolists\Components\TextEntry::make('tanggal_pembelian')
                ->label('Tanggal Pembelian'),
            Infolists\Components\TextEntry::make('pemasok.nama')
                ->label('Nama Penjual'),
            Infolists\Components\TextEntry::make('total')
        ]);
    }
}
