<?php

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class StockAlerts extends TableWidget
{
    protected static ?string $heading = 'Alertes de stock';
protected int|string|array $columnSpan = 1;
    protected function getTableQuery(): Builder
    {
        return ProductVariant::query()
            ->with([
                'product',
                'size',
                'color',
            ])
            ->whereColumn(
                'stock_quantity',
                '<=',
                'alert_threshold'
            )
            ->orderBy('stock_quantity')
            ->limit(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU'),

                Tables\Columns\TextColumn::make('size.name')
                    ->label('Taille')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('color.name')
                    ->label('Couleur')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->badge()
                    ->color(function ($state): string {
                        return (int) $state === 0
                            ? 'danger'
                            : 'warning';
                    }),

                Tables\Columns\TextColumn::make('alert_threshold')
                    ->label('Seuil')
                    ->numeric(),
            ])
            ->paginated(false);
    }
}