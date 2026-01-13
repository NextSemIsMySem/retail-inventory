<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                    ->label('Stock')
                    ->formatStateUsing(fn($state) => $state < 10 ? "Low ($state)" : $state),

                BadgeColumn::make('status')
                    ->icons([
                        'heroicon-o-check-circle' => 'active',
                        'heroicon-o-x-circle' => 'discontinued',
                    ])
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

                Filter::make('low stock')
                    ->label('low stock')
                    ->query(fn($query) => $query->where('quantity', '<', 10)),

                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),

                Action::make('Increase Stock')
                    ->label('Add Stock')
                    ->icon('heroicon-o-plus')
                    ->action(
                        fn(Product $record) =>
                        $record->increment('quantity', 5)
                    )
                    ->requiresConfirmation(),

                Action::make('Decrease Stock')
                    ->label('Reduce Stock')
                    ->icon('heroicon-o-minus')
                    ->action(
                        fn(Product $record) =>
                        $record->decrement('quantity', 1)
                    )
                    ->requiresConfirmation(),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Start by adding products from Apple, Samsung, or Xiaomi.');
    }
}
