<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static function getProductPrice(mixed $productId): float
    {
        if (blank($productId)) {
            return 0.0;
        }

        return (float) (Product::query()->whereKey($productId)->value('price') ?? 0);
    }

    protected static function getProductStock(mixed $productId): ?int
    {
        if (blank($productId)) {
            return null;
        }

        $stock = Product::query()->whereKey($productId)->value('quantity');

        return filled($stock) ? (int) $stock : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                    Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn(): bool => $this->ownerRecord->status === 'completed')
                        ->live()
                        ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                            $price = static::getProductPrice($state);
                            $set('price', $price);

                            $quantity = (int) ($get('quantity') ?? 1);

                            if ($quantity < 1) {
                                $quantity = 1;
                                $set('quantity', 1);
                            }

                            $set('subtotal', round($price * $quantity, 2));
                        }),

                    TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn(Get $get): ?int => static::getProductStock($get('product_id')))
                        ->validationMessages([
                                'max' => 'Requested quantity exceeds available stock. Only :max left for the selected product.',
                            ])
                        ->helperText(fn(Get $get): ?string => filled($stock = static::getProductStock($get('product_id')))
                            ? "Available stock: {$stock}"
                            : null)
                        ->default(1)
                        ->required()
                        ->disabled(fn(): bool => $this->ownerRecord->status === 'completed')
                        ->live()
                        ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                            $quantity = (int) $state;

                            if ($quantity < 1) {
                                $quantity = 1;
                                $set('quantity', 1);
                            }

                            $price = (float) ($get('price') ?? 0);
                            $set('subtotal', round($price * $quantity, 2));
                        }),

                    TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('subtotal')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('product.name')
                        ->label('Product')
                        ->searchable(),
                    TextColumn::make('quantity')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('price')
                        ->money('MYR')
                        ->sortable(),
                    TextColumn::make('subtotal')
                        ->money('MYR')
                        ->sortable(),
                ])
            ->headerActions([
                    CreateAction::make()
                        ->disabled(fn(): bool => $this->ownerRecord->status === 'completed')
                        ->after(fn() => $this->ownerRecord->recalculateTotal()),
                ])
            ->actions([
                    EditAction::make()
                        ->disabled(fn(): bool => $this->ownerRecord->status === 'completed')
                        ->after(fn() => $this->ownerRecord->recalculateTotal()),
                    DeleteAction::make()
                        ->disabled(fn(): bool => $this->ownerRecord->status === 'completed')
                        ->after(fn() => $this->ownerRecord->recalculateTotal()),
                ])
            ->bulkActions([
                    DeleteBulkAction::make()
                        ->disabled(fn(): bool => $this->ownerRecord->status === 'completed')
                        ->after(fn() => $this->ownerRecord->recalculateTotal()),
                ]);
    }
}
