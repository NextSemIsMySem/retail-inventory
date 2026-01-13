<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Product name')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$')
                    ->inputMode('decimal'),
                TextInput::make('quantity')
                    ->label('Stock')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('warranty_months')
                    ->label('Warranty (months)')
                    ->integer()
                    ->minValue(0)
                    ->nullable(),
                Select::make('status')
                    ->options(['active' => 'Active', 'discontinued' => 'Discontinued'])
                    ->default('active')
                    ->required(),
            ]);
    }
}
