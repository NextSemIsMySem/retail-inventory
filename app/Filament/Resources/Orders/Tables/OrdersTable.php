<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('order_number')
                        ->searchable(),
                    TextColumn::make('customer_name')
                        ->searchable(),
                    TextColumn::make('status')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'pending' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'gray',
                        }),
                    TextColumn::make('total_amount')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ->recordUrl(fn(Order $record): string => $record->status === 'completed'
                ? OrderResource::getUrl('view', ['record' => $record])
                : OrderResource::getUrl('edit', ['record' => $record]))
            ->filters([
                    SelectFilter::make('status')
                        ->label('Status')
                        ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ]),
                ])
            ->recordActions([
                    EditAction::make()
                        ->disabled(fn(Order $record): bool => $record->status === 'completed'),
                ])
            ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make()
                            ->disabled(fn($records): bool => $records->contains(fn(Order $record): bool => $record->status === 'completed')),
                    ]),
                ]);
    }
}
