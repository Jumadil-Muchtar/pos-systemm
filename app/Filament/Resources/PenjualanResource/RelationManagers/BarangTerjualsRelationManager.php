<?php

namespace App\Filament\Resources\PenjualanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangTerjualsRelationManager extends RelationManager
{
    protected static string $relationship = 'barang_terjuals';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('barang.produk.nama'),
                Tables\Columns\TextColumn::make('barang.harga_jual')
                    ->label('Harga')
                    ->money('Rp. '),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Sub-total')
                    ->money('Rp. '),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
