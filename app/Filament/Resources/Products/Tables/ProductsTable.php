<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return static::table($table);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable(),

                TextColumn::make('sku')
                    ->searchable(),

                TextColumn::make('price')
                    ->money('MYR'),

                TextColumn::make('quantity')
                    ->badge()
                    ->color(fn($state) => $state < 10 ? 'danger' : 'success')
                    ->label('Stock'),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'discontinued',
                    ]),

                TextColumn::make('created_at')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'discontinued' => 'Discontinued',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
