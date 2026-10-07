<?php

namespace App\Filament\Resources\ProductVariants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductVariantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('size.name')
                    ->label('Size')
                    ->placeholder('-'),
                TextEntry::make('color.name')
                    ->label('Color')
                    ->placeholder('-'),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('purchase_price')
                    ->money(),
                TextEntry::make('selling_price')
                    ->money(),
                TextEntry::make('stock_quantity')
                    ->numeric(),
                TextEntry::make('alert_threshold')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
