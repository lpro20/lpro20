<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('reference')
                    ->required(),
                DateTimePicker::make('sale_date')
                    ->required(),
                Select::make('status')
                    ->options(SaleStatus::class)
                    ->default('draft')
                    ->required(),
                Select::make('payment_method')
                    ->options(PaymentMethod::class)
                    ->default('cash')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('amount_paid')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('change_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
