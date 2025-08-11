<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Filament\Resources\ProdukResource\RelationManagers;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('nama')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('kode')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('kategori_id')
                    ->relationship('kategori', 'nama')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nama')
                            ->maxLength(255)
                            ->required(),
                    ])->required(),
                Forms\Components\FileUpload::make('gambar')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambar'),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok')
                    ->state(function (Produk $produk) : int {
                        return $produk->barangs->whereNotIn('status', ['terjual'])->count();
                    }),
                Tables\Columns\TextColumn::make('dipajang')
                    ->state(function (Produk $produk) : int {
                        return $produk->barangs->where('status', 'dipajang')->count();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kedaluwarsa')
                    ->state(function (Produk $produk) {
                        return $produk->barangs->sortBy('tanggal_kedaluwarsa')->last()->pluck('tanggal_kedaluwarsa');
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
            'view' => Pages\ViewProduk::route('/{record}'),
        ];
    }
    public static function infolist(Infolist $infolist) : Infolist {
        return $infolist->schema([
            Section::make('Informasi Produk')->schema([
                Infolists\Components\ImageEntry::make('gambar')->label('Foto Produk'),
                Infolists\Components\TextEntry::make('nama'),
                Infolists\Components\TextEntry::make('kode'),
                Infolists\Components\TextEntry::make('kategori.nama'),
                Infolists\Components\TextEntry::make('stok')
                    ->label('Jumlah barang (Stok) tersedia')
                    ->state(function (Produk $record) : int{
                        return $record->barangs->whereNotIn('status', ['terjual'])->count();
                    }),
                Infolists\Components\TextEntry::make('dipajang')
                    ->label('Jumlah barang yang dipajang')
                    ->state(function (Produk $record) : int {
                        return $record->barangs->where('status', 'dipajang')->count();
                    }),  
                Infolists\Components\TextEntry::make('terjual')
                    ->label('Jumlah barang yang telah terjual')
                    ->state(function (Produk $record) : int {
                        return $record->barangs->where('status', 'terjual')->count();
                    }),  
            ])->collapsible(),
                    
        ]);
    }
    public static function getEloquentQuery() : Builder{
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class
            ]);
    }
}
