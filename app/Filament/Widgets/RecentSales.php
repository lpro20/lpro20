<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSales extends TableWidget
{
    protected static ?string $heading = 'Dernières ventes';
protected int|string|array $columnSpan = 'full';
    protected function getTableQuery(): Builder
    {
        return Sale::query()
            ->with([
                'customer',
            ])
            ->where('status', SaleStatus::COMPLETED->value)
            ->latest('sale_date')
            ->limit(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Client')
                    ->placeholder('Client comptant'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Montant')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: ' '
                    )
                    ->suffix(' FCFA'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(
                        fn (PaymentMethod|string|null $state): string =>
                            $state instanceof PaymentMethod
                                ? $state->label()
                                : (PaymentMethod::tryFrom((string) $state)?->label()
                                    ?? '-')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(
                        fn (SaleStatus|string|null $state): string =>
                            $state instanceof SaleStatus
                                ? $state->label()
                                : (SaleStatus::tryFrom((string) $state)?->label()
                                    ?? '-')
                    )
                    ->color('success'),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('sale_date', 'desc')
            ->paginated(false);
    }
}