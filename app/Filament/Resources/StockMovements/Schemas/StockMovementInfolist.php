<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('productVariant.id')
                    ->label('Product variant'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('stock_before')
                    ->numeric(),
                TextEntry::make('stock_after')
                    ->numeric(),
                TextEntry::make('reference')
                    ->placeholder('-'),
                TextEntry::make('reason')
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
