<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('supplier.name')
                    ->label('Supplier'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('reference'),
                TextEntry::make('purchase_date')
                    ->date(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('shipping_cost')
                    ->money(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
