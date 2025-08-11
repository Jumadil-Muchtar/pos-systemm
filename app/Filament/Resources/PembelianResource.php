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
use DesignTheBox\BarcodeField\Forms\Components\BarcodeInput;

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
                    ->relationship()
                    ->schema([
                        BarcodeInput::make('barcode')
                            ->icon('heroicon-o-rectangle-stack') 
                            ->required(),
                        Forms\Components\Select::make('produk_id')
                            ->label('Nama Produk')
                            ->options(Produk::all()->pluck('nama', 'id'))
                            ->searchable(),
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
                //
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
}
