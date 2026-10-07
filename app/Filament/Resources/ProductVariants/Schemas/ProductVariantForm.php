<?php

namespace App\Filament\Resources\ProductVariants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('size_id')
                    ->relationship('size', 'name'),
                Select::make('color_id')
                    ->relationship('color', 'name'),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('purchase_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('alert_threshold')
                    ->required()
                    ->numeric()
                    ->default(5),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
