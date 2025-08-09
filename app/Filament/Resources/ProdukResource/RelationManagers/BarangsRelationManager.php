<?php

namespace App\Filament\Resources\ProdukResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangsRelationManager extends RelationManager
{
    protected static string $relationship = 'barangs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tanggal_kedaluwarsa')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal_kedaluwarsa')
            ->columns([
                Tables\Columns\TextColumn::make('pembelian.tanggal_pembelian')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_kedaluwarsa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable(),
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
