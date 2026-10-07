<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Customer')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('reference'),
                TextEntry::make('sale_date')
                    ->dateTime(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payment_method')
                    ->badge(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('amount_paid')
                    ->numeric(),
                TextEntry::make('change_amount')
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
