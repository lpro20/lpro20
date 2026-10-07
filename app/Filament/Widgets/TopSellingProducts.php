<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\ProductVariant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSellingProducts extends TableWidget
{
    protected static ?string $heading = 'Produits les plus vendus';
protected int|string|array $columnSpan = 1;
    protected function getTableQuery(): Builder
    {
        return ProductVariant::query()
            ->with([
                'product',
                'size',
                'color',
            ])
            ->withSum(
                [
                    'saleItems as total_quantity' => function (Builder $query) {
                        $query->whereHas('sale', function (Builder $saleQuery) {
                            $saleQuery->where(
                                'status',
                                SaleStatus::COMPLETED->value
                            );
                        });
                    },
                ],
                'quantity'
            )
            ->whereHas('saleItems.sale', function (Builder $query) {
                $query->where(
                    'status',
                    SaleStatus::COMPLETED->value
                );
            })
            ->orderByDesc('total_quantity')
            ->limit(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU'),

                Tables\Columns\TextColumn::make('size.name')
                    ->label('Taille')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('color.name')
                    ->label('Couleur')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Quantité vendue')
                    ->numeric(),
            ])
            ->paginated(false);
    }
}