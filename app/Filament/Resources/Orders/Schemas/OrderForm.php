<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn() => 'ORD-' . now()->timestamp)
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),
                TextInput::make('customer_name'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->helperText('Pending: order is in progress. Completed: finalised (stock deducted, editing locked). Cancelled: order was cancelled.')
                    ->default('pending')
                    ->disabled(fn(string $operation): bool => $operation === 'create')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
